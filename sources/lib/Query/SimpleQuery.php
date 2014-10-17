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
use PommProject\Foundation\Query\ListenerInterface;

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
    protected $listeners = [];

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
        $resource = $this
            ->getSession()
            ->getConnection()
            ->sendQueryWithParameters(QueryParameterExpander::order($sql), $parameters)
            ;
        $end = microtime(true);

        $iterator = new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );
        $this->sendNotification(
            'post',
            [
                'result_count' => $iterator->count(),
                'time_ms'           => sprintf("%03.1f", ($end - $start) * 1000),

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
     * @return ConvertedResultIterator
     */
    protected function doQuery($sql, array $parameters)
    {
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
     * registerListener
     *
     * Add a new listener to the pooler.
     *
     * @access public
     * @param ListenerInterface $listener
     * @return SimpleQuery
     */
    public function registerListener(ListenerInterface $listener)
    {
        $this->listerners[] = $listener;

        return $this;
    }

    /**
     * notify
     *
     * Send a notification to all listeners.
     *
     * @access protected
     * @param  string $event_type
     * @param  array $data
     * @return SimpleQuery
     */
    protected function sendNotification($event_type, array $data)
    {
        foreach ($this->listeners as $listener) {
            $listener->notify($event_type, $data);
        }

        return $this;
    }
}
