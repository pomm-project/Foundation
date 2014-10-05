<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Observer;

use PommProject\Foundation\Exception\NotificationException;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Session;

/**
 * Observer
 *
 * Observer session client.
 * Listen to notifications sent to the server.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see Client
 */
class Observer extends Client
{
    protected $channel;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  string $channel
     * @return void
     */
    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    /**
     * getClientType
     *
     * @see Client
     */
    public function getClientType()
    {
        return 'observer';
    }

    /**
     * getClientIdentifier
     *
     * @see Client
     */
    public function getClientIdentifier()
    {
        return $this->channel;
    }

    /**
     * initialize
     *
     * @see Client
     */
    public function initialize(Session $session)
    {
        parent::initialize($session);
        $this
            ->executeAnonymousQuery(
                sprintf(
                    "listen %s",
                    $this->escapeIdentifier($this->channel)
                )
            );
    }

    /**
     * shutdown
     *
     * @see Client
     */
    public function shutdown()
    {
        $this->executeAnonymousQuery(
            sprintf(
                "unlisten %s",
                $this->escapeIdentifier($this->channel)
            )
        );
    }

    /**
     * getNotification
     *
     * Check if a notification is pending. If so, the payload is returned.
     * Otherwise, null is returned.
     *
     * @access public
     * @return array
     */
    public function getNotification()
    {
        return $this
            ->getSession()
            ->getConnection()
            ->getNotification($this->channel)
            ;
    }

    /**
     * throwNotification
     *
     * Check if a notification is pending. If so, a NotificationException is thrown.
     *
     * @access public
     * @throw  NotificationException
     * @return Observer $this
     */
    public function throwNotification()
    {
        $notification = $this->getNotification();

        if ($notification !== null) {
            throw new NotificationException($notification);
        }

        return $this;
    }

    /**
     * executeAnonymousQuery
     *
     * Proxy for Connection::executeAnonymousQuery()
     *
     * @access protected
     * @param  string   $sql
     * @return Observer $this
     * @see Connection
     */
    protected function executeAnonymousQuery($sql)
    {
        $this
            ->getSession()
            ->getConnection()
            ->executeAnonymousQuery($sql)
            ;

        return $this;
    }

    /**
     * escapeIdentifier
     *
     * Proxy for Connection::escapeIdentifier()
     *
     * @access protected
     * @param  string $string
     * @return string
     * @see Connection
     */
    protected function escapeIdentifier($string)
    {
        return $this
            ->getSession()
            ->getConnection()
            ->escapeIdentifier($string)
            ;
    }
}
