<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Client;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Session\Session;

/**
 * ClientPoolerTrait
 *
 * Trait for client poolers. ClientPooler instances are factories for
 * Session's Client. The workflow of the ClientPooler is the following:
 * When a client is called
 * 1   It is fetched from the pool
 * 2.0 If no client is found:
 * 2.1 The createClient() method is triggered.
 * 2.2 The client is registered to the session
 * 3   The client is returned back
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPoolerInterface
 * @abstract
 */
trait ClientPoolerTrait
{
    private $session;

    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    abstract public function getPoolerType();

    /**
     * createClient
     *
     * Create a new client.
     *
     * @abstract
     * @access protected
     * @param  string $identifier
     * @return Client
     */
    abstract protected function createClient($identifier);

    /**
     * register
     *
     * @see ClientPoolerInterface
     */
    public function register(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * getClient
     *
     * Basic getClient method.
     *
     * @access public
     * @param  string $identifier
     * @return Client
     * @see    ClientInterface
     */
    public function getClient($identifier)
    {
        $client = $this->getClientFromPool($identifier);

        if ($client === null) {
            $client = $this->createClient($identifier);
            $this->getSession()->registerClient($client);
        }

        return $client;
    }

    /**
     * getClientFromPool
     *
     * How the pooler fetch a client from the pool.
     *
     * @access protected
     * @param  string      $identifier
     * @return Client|null
     */
    protected function getClientFromPool($identifier)
    {
        return $this
            ->getSession()
            ->getClient($this->getPoolerType(), $identifier)
            ;
    }

    /**
     * getSession
     *
     * Check if the session is set and return it.
     *
     * @access protected
     * @return Session
     * @throws FoundationException
     */
    protected function getSession()
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Client pooler '%s' is not initialized, session not set.", get_class($this)));
        }

        return $this->session;
    }
}
