<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Listener\SendNotificationTrait;
use PommProject\Foundation\QueryManager\QueryParameterParserTrait;

/**
 * SimpleQueryManager
 *
 * Query system as a client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class SimpleQueryManager extends Client
{
    use SendNotificationTrait;
    use QueryParameterParserTrait;

    /**
     * query
     *
     * Perform a simple escaped query and return converted result iterator.
     *
     * @access public
     * @param  sting $sql
     * @param  array $parameters
     * @return ConvertedResultIterator
     */
    public function query($sql, array $parameters = [])
    {
        $this->sendNotification(
            'query:pre',
            [
                'sql'           => $sql,
                'parameters'    => $parameters,
                'session_stamp' => $this->getSession()->getStamp(),
            ]
        );
        $start    = microtime(true);
        $resource = $this->doQuery($sql, $parameters);
        $end      = microtime(true);

        $iterator = new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );
        $this->sendNotification(
            'query:post',
            [
                'result_count' => $iterator->count(),
                'time_ms'      => sprintf("%03.1f", ($end - $start) * 1000),
            ]
        );

        return $iterator;
    }

    /**
     * doQuery
     *
     * Perform the query
     *
     * @access protected
     * @param  string $sql
     * @param  array $parameters
     * @return ResultHandler
     */
    protected function doQuery($sql, array $parameters)
    {
        return $this
            ->getSession()
            ->getConnection()
            ->sendQueryWithParameters(
                $this->orderParameters($sql),
                $this->prepareArguments($sql, $parameters)
            )
            ;
    }

    /**
     * prepareArguments
     *
     * Prepare and convert $parameters if needed.
     *
     * @access protected
     * @param  string   $sql
     * @param  array    $parameters
     * @return array    $parameters
     */
    protected function prepareArguments($sql, array $parameters)
    {
        $types = $this->getParametersType($sql);

        foreach ($parameters as $index => $value) {
            if ($types[$index] !== null) {
                $parameters[$index] = $this
                    ->getSession()
                    ->getClientUsingPooler('converter', $types[$index])
                    ->toPgStandardFormat($value, $types[$index])
                    ;
            }
        }

        return $parameters;
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'query_manager';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return get_class($this);
    }
}
