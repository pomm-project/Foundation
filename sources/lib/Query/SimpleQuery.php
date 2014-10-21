<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Query;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\QueryParameterExpander;
use PommProject\Foundation\ConvertedResultIterator;

/**
 * SimpleQuery
 *
 * Query system as a client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class SimpleQuery extends Client
{
    /**
     * query
     *
     * Perform a simple escaped query.
     *
     * @access public
     * @param  sting $sql
     * @param  array $parameters
     * @return ConvertedResultIterator
     */
    public function query($sql, array $parameters = [])
    {
        $this->sendNotification(
            'pre',
            [
                'sql'        => $sql,
                'parameters' => $parameters,
            ]
        );
        $start = microtime(true);
        $resource = $this->doQuery($sql, $parameters);
        $end = microtime(true);

        $iterator = new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );
        $this->sendNotification(
            'post',
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
            ->sendQueryWithParameters(QueryParameterExpander::order($sql), $parameters)
            ;
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'query';
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

    /**
     * sendNotification
     *
     * Send notification to the listener pooler.
     *
     * @access protected
     * @param  string $name
     * @param  array $data
     * @return SimpleQuery $this
     */
    protected function sendNotification($name, array $data)
    {
        $this
            ->getSession()
            ->getPoolerForType('listener')
            ->notify($name, $data)
            ;

        return $this;
    }
}
