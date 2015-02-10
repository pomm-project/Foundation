<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\QueryManager\QueryParameterParserTrait;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\QueryParameterExpander;
use PommProject\Foundation\Client\Client;

/**
 * PreparedQuery
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PreparedQuery extends Client
{
    use QueryParameterParserTrait;

    protected $sql;
    private $is_prepared = false;
    private $identifier;
    private $converters = null;

    /**
     * getSignatureFor
     *
     * Returns a hash for a given sql query.
     *
     * @static
     * @access public
     * @param  string $sql Sql query
     * @return string
     */
    public static function getSignatureFor($sql)
    {
        return md5($sql);
    }

    /**
     * __construct
     *
     * Build the prepared query.
     *
     * @access public
     * @param  string $sql SQL query
     * @return void
     */
    public function __construct($sql)
    {
        if (empty($sql)) {
            throw new FoundationException("Can not prepare an empty query.");
        }

        $this->sql        = $sql;
        $this->identifier = static::getSignatureFor($sql);
    }

    /**
     * @see ClientPoolerInterface
     */
    public function getClientType()
    {
        return 'prepared_query';
    }

    /**
     * getClientIdentifier
     *
     * Return the query identifier.
     *
     * @access public
     * @return string Query identifier.
     */
    public function getClientIdentifier()
    {
        return $this->identifier;
    }

    /**
     * shutdown
     *
     * Deallocate the statement in the database.
     *
     * @see ClientInterface
     */
    public function shutdown()
    {
        if ($this->is_prepared === true) {
            $this
                ->getSession()
                ->getConnection()
                ->executeAnonymousQuery(sprintf(
                    "deallocate %s",
                    $this->getSession()->getConnection()->escapeIdentifier($this->getClientIdentifier())
                ));

            $this->is_prepared = false;
        }
    }

    /**
     * execute
     *
     * Launch the query with the given parameters.
     *
     * @access public
     * @param  array    $values Query parameters
     * @return Resource
     */
    public function execute(array $values = [])
    {
        if ($this->is_prepared === false) {
            $this->prepare();
        }

        return $this
            ->getSession()
            ->getConnection()
            ->sendExecuteQuery(
                $this->getClientIdentifier(),
                $this->prepareValues($this->sql, $values),
                $this->sql
            );
    }

    /**
     * prepare
     *
     * Send the query to be prepared by the server.
     *
     * @access protected
     * @return PreparedQuery $this
     */
    protected function prepare()
    {
        $this
            ->getSession()
            ->getConnection()
            ->sendPrepareQuery(
                $this->getClientIdentifier(),
                $this->orderParameters($this->sql)
            );
        $this->is_prepared = true;

        return $this;
    }

    /**
     * getSql
     *
     * Get the original SQL query
     *
     * @access public
     * @return string SQL query
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * prepareValues
     *
     * Prepare parameters to be sent.
     *
     * @access protected
     * @param  mixed    $sql
     * @param  array    $values
     * @return array    $prepared_values
     */
    protected function prepareValues($sql, array $values)
    {
        if ($this->converters === null ) {
            $this->prepareConverters($sql);
        }

        foreach ($values as $index => $value) {
            if (isset($this->converters[$index])) {
                $values[$index] = $this->converters[$index]->toPgStandardFormat($value, '', $this->getSession());
            }
        }

        return $values;
    }

    /**
     * prepareConverters
     *
     * Store converters needed for the query parameters.
     *
     * @access protected
     * @param mixed             $sql
     * @return PreparedQuery    $this
     */
    protected function prepareConverters($sql)
    {
        foreach ($this->getParametersType($sql) as $index => $type) {
            if ($type === '') {
                $this->converters[$index] = null;
            } else {
                $this->converters[$index] = $this
                    ->getSession()
                    ->getClientUsingPooler('converter', $type)
                    ->getConverter()
                    ;
            }
        }

        return $this;
    }
}
