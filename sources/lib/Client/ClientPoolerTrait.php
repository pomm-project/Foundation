<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientPoolerInterface
 * @abstract
 */
trait ClientPoolerTrait
{
    private ?Session $session = null;

    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    abstract public function getPoolerType(): string;

    /**
     * createClient
     *
     * Create a new client.
     *
     * @abstract
     * @access protected
     * @param  string $identifier
     * @return ClientInterface
     */
    abstract protected function createClient(string $identifier): ClientInterface;

    /**
     * register
     *
     * @see ClientPoolerInterface
     */
    public function register(Session $session): ClientPoolerInterface
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
     * @param string $identifier
     * @return ClientInterface
     * @throws FoundationException
     * @see    ClientInterface
     */
    public function getClient(string $identifier): ClientInterface
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
     * @param string $identifier
     * @return ClientInterface|null
     * @throws FoundationException
     */
    protected function getClientFromPool(string $identifier): ?ClientInterface
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
    protected function getSession(): Session
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Client pooler '%s' is not initialized, session not set.", $this::class));
        }

        return $this->session;
    }
}
