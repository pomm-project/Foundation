<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Observer;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Exception\NotificationException;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Session\Session;

/**
 * Observer
 *
 * Observer session client.
 * Listen to notifications sent to the server.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       Client
 */
class Observer extends Client
{
    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param string $channel
     */
    public function __construct(protected string $channel)
    {
    }

    /**
     * getClientType
     *
     * @see Client
     */
    public function getClientType(): string
    {
        return 'observer';
    }

    /**
     * getClientIdentifier
     *
     * @see Client
     */
    public function getClientIdentifier(): string
    {
        return $this->channel;
    }

    /**
     * initialize
     *
     * @see Client
     */
    public function initialize(Session $session): void
    {
        parent::initialize($session);
        $this->restartListening();
    }

    /**
     * shutdown
     *
     * @see Client
     */
    public function shutdown(): void
    {
        $this->unlisten($this->channel);
    }

    /**
     * getNotification
     *
     * Check if a notification is pending. If so, the payload is returned.
     * Otherwise, null is returned.
     *
     * @access public
     * @return array|null
     * @throws FoundationException
     */
    public function getNotification(): ?array
    {
        return $this
            ->getSession()
            ->getConnection()
            ->getNotification()
            ;
    }

    /**
     * restartListening
     *
     * Send a LISTEN command to the backend. This is called in the initialize()
     * method but it can be unlisten if the listen command took place in a
     * transaction.
     *
     * @access public
     * @return Observer $this
     * @throws FoundationException
     */
    public function restartListening(): Observer
    {
        return $this->listen($this->channel);
    }

    /**
     * listen
     *
     * Start to listen on the given channel. The observer automatically starts
     * listening when registered against the session.
     * NOTE: When listen is issued in a transaction it is unlisten when the
     * transaction is committed or rollback.
     *
     * @access protected
     * @param string $channel
     * @return Observer $this
     * @throws FoundationException
     */
    protected function listen(string $channel): Observer
    {
        $this
            ->executeAnonymousQuery(
                sprintf(
                    "listen %s",
                    $this->escapeIdentifier($channel)
                )
            );

        return $this;
    }

    /**
     * unlisten
     *
     * Stop listening to events.
     *
     * @access protected
     * @param string $channel
     * @return Observer $this
     *
     */
    protected function unlisten(string $channel): Observer
    {
        $this->executeAnonymousQuery(
            sprintf(
                "unlisten %s",
                $this->escapeIdentifier($channel)
            )
        );

        return $this;
    }

    /**
     * throwNotification
     *
     * Check if a notification is pending. If so, a NotificationException is thrown.
     *
     * @access public
     * @return Observer $this
     *@throws  NotificationException|FoundationException
     */
    public function throwNotification(): Observer
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
     * @param string $sql
     * @return Observer $this
     * @throws FoundationException
     * @see Connection
     */
    protected function executeAnonymousQuery(string $sql): Observer
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
     * @param string $string
     * @return string
     * @throws FoundationException
     * @see Connection
     */
    protected function escapeIdentifier(string $string): string
    {
        return $this
            ->getSession()
            ->getConnection()
            ->escapeIdentifier($string)
            ;
    }
}
