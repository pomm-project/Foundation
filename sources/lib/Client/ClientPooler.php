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
use PommProject\Foundation\Session;

/**
 * ClientPooler
 *
 * Base class for client poolers. ClientPooler instances are factories for
 * Session's Client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPoolerInterface
 * @abstract
 */
abstract class ClientPooler implements ClientPoolerInterface
{
    private $session;

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
     */
    protected function getSession()
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Client pooler '%s' is not initialized, session not set.", get_class($this)));
        }

        return $this->session;
    }
}
