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

use PommProject\Foundation\Session;
use PommProject\Foundation\QueryParameterExpander;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Exception\ConnectionException;

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
    protected $session;
    protected $sql;
    private   $is_prepared = false;
    private   $identifier;

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
     * @param  Session $session
     * @param  string     $sql        SQL query
     * @return void
     */
    public function __construct($sql)
    {
        $this->sql        = $sql;
        $this->identifier = static::getSignatureFor($sql);
    }

    /**
     * @see ClientPoolerInterface
     */
    public function getClientType()
    {
        return 'prepared_statement';
    }

    /**
     * @see ClientPoolerInterface
     */
    public function initialize(Session $session)
    {
        $this->session = $session;

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
        $res = @pg_execute($this->session->getHandler(), sprintf("DEALLOCATE %s", $this->session->escapeIdentifier($this->getName())));

        if ($res === false) {
            throw new PommException(sprintf("Could not deallocate statement «%s».", $this->getName()));
        }

        $this->is_prepared = false;
    }

    /**
     * execute
     *
     * Launch the query with the given parameters.
     *
     * @access public
     * @param  Array    $values Query parameters
     * @return Resource
     */
    public function execute(array $values = array())
    {
        if ($this->is_prepared === false) {
            $this->prepare();
        }

        if (pg_send_execute($this->session->getHandler(), $this->name, $this->prepareValues($values)) === false) {
            throw new ConnectionException(sprintf("Connection error while executing query '%s'.", $this->name));
        }

        return $this->session->getQueryResult($this->sql);
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
        if (pg_prepare($this->session->getHandler(), $this->name, QueryParameterExpander::order($sql)) === false) {
            throw new PommException(sprintf("Could not prepare statement «%s».", $sql));
        }

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
     * Process the values for the query so they are understandable by Postgres.
     *
     * @access private
     * @param  array $values Query parameters
     * @return array
     */
    private function prepareValues(array $values)
    {
        foreach ($values as $index => $value) {
            if ($value instanceof \DateTime) {
                $values[$index] = $value->format('Y-m-d H:i:s.uP');
            }
        }

        return $values;
    }
}
