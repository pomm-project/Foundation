<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Listener;

use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Listener\Listener;
use PommProject\Foundation\Session\Session;

/**
 * ListenerPooler
 *
 * Pooler for listener clients.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class ListenerPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'listener';
    }


    /**
     * createClient
     *
     * See @ClientPooler
     */
    protected function createClient($identifier)
    {
        return new Listener($identifier);
    }

    /**
     * notify
     *
     * Send a notification to clients.
     * Client identifiers may be a single client name, an array of client or
     * '*' to notify all clients.
     * Event name may use ':' to split indicate additional information (ie type
     * of payload). Events sent to 'pika', 'pika:chu' will both notify client
     * 'pika'.
     *
     * @access public
     * @param  string|array     $identifiers
     * @param  array            $data
     * @return ListenerPooler   $this
     */
    public function notify($identifiers, array $data)
    {
        $this->getSession()->hasLogger() &&
            $this->getSession()->getLogger()->info(
                "ListenerPooler: notification received.",
                [
                    'receivers' => $identifiers,
                    'data'      => $data,
                ]
            );

        if (is_scalar($identifiers)) {
            if ($identifiers === '*') {
                return $this->notifyAll($data);
            }

            $identifiers = [ $identifiers ];
        }

        foreach ($identifiers as $identifier) {
            $client_name = strpos($identifier, ':') !== false
                ? substr($identifier, 0, strpos($identifier, ':'))
                : $identifier
                ;

            $client = $this
                ->getSession()
                ->getClient($this->getPoolerType(), $client_name)
                ;

            if ($client !== null) {
                $client->notify($identifier, $data);
            }
        }

        return $this;
    }

    /**
     * notifyAll
     *
     * Notify all existing clients.
     *
     * @access protected
     * @param  array            $data
     * @return ListenerPooler   $this
     */
    protected function notifyAll(array $data)
    {
        foreach (
            $this->getSession()->getAllClientForType($this->getPoolerType()) 
            as $client) {
                $client->notify('*', $data);
            }

        return $this;
    }

}
