<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Session;

use PommProject\Foundation\Inflector;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Client\ClientHolder;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Exception\FoundationException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Session
 *
 * Public API to share the database connection. IO are shared amongst
 * clients which are stored in a ClientHolder.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see LoggerAwareInterface
 */
class Session implements LoggerAwareInterface
{
    protected $connection;
    protected $client_holder;
    protected $client_poolers = [];

    use LoggerAwareTrait;

    /**
     * __construct
     *
     * Constructor.
     * In order to create a physical connection to the database, it requires a
     * 'dsn' parameter from the ParameterHolder.
     *
     * @access public
     * @param  Connection $connection
     * @param  ConverterHolder
     * @return null
     */
    public function __construct(
        Connection      $connection,
        ClientHolder    $client_holder = null
    ) {
        $this->connection    = $connection;
        $this->client_holder = $client_holder === null
            ? new ClientHolder
            : $client_holder
            ;
    }

    /**
     * __destruct
     *
     * Gently shutdown all clients when the Session is getting down prior to
     * connection termination.
     *
     * @access public
     * @return null
     */
    public function __destruct()
    {
        $this->client_holder->shutdown();
        $this->client_poolers = [];
        $this->connection->close();
    }

    /**
     * getLogger
     *
     * Return the logger if any.
     *
     * @access public
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * hasLogger
     *
     * Return true if a logger is set.
     *
     * @access public
     * @return bool
     */
    public function hasLogger()
    {
        return (bool) ($this->logger !== null);
    }

    /**
     * getConnection
     *
     * Return the database connection.
     *
     * @access public
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * registerClient
     *
     * Initialize a connection client with context and register it in the
     * client holder.
     *
     * @access public
     * @param  Client  $client
     * @return Session $this
     */
    public function registerClient(ClientInterface $client)
    {
        $client->initialize($this);
        $this->client_holder->add($client);

        return $this;
    }

    /**
     * getClient
     *
     * Return a Client from its type and identifier.
     *
     * @access public
     * @param  string $type
     * @param  string $identifier
     * @return Client or null if not found.
     */
    public function getClient($type, $identifier)
    {
        return $this->client_holder->get($type, $identifier);
    }

    /**
     * hasClient
     *
     * Tell if a client exist or not.
     *
     * @access public
     * @param  string $type
     * @param  string $name
     * @return bool
     */
    public function hasClient($type, $name)
    {
        return $this->client_holder->has($type, $name);
    }

    /**
     * registerClientPooler
     *
     * Add or replace a Client pooler for the specified type.
     *
     * @access public
     * @param  string                $type
     * @param  ClientPoolerInterface $client_pooler
     * @return Session               $this
     */
    public function registerClientPooler(ClientPoolerInterface $client_pooler)
    {
        if ($client_pooler->getPoolerType() == null) {
            throw new \InvalidArgumentException(sprintf("Can not register a pooler for the empty type."));
        }

        $client_pooler->register($this);
        $this->client_poolers[$client_pooler->getPoolerType()] = $client_pooler;

        return $this;
    }

    /**
     * hasPoolerForType
     *
     * Tell if a pooler exist or not.
     *
     * @access public
     * @param  string $type
     * @return bool
     */
    public function hasPoolerForType($type)
    {
        return (bool) (isset($this->client_poolers[$type]));
    }

    /**
     * getPoolerForType
     *
     * Get the registered for the given type.
     *
     * @access public
     * @param  string                $type
     * @throw  FoundationException if pooler does not exist
     * @return ClientPoolerInterface
     */
    public function getPoolerForType($type)
    {
        if (!$this->hasPoolerForType($type)) {
            throw new FoundationException(
                sprintf(
                    "No pooler registered for type '%s'. Poolers are available for types {%s}.",
                    $type,
                    join(', ', $this->getRegisterPoolersNames())
                )
            );
        }

        return $this->client_poolers[$type];
    }

    /**
     * getAllClientForType
     *
     * Return all instances of clients for a given type.
     *
     * @access public
     * @param  string $type
     * @return ClientInterface
     */
    public function getAllClientForType($type)
    {
        return $this->client_holder->getAllFor($type);
    }

    /**
     * getClientUsingPooler
     *
     * Summon a pooler to retrieve a client. If the pooler does not exist, a
     * FoundationException is thrown.
     *
     * @access public
     * @param  string          $type
     * @param  string          $identifier
     * @return ClientInterface
     */
    public function getClientUsingPooler($type, $identifier)
    {
        return $this->getPoolerForType($type)->getClient($identifier);
    }

    /**
     * __call
     *
     * Create handy methods to access clients through a pooler.
     *
     * @access public
     * @param  string          $method
     * @param  array           $arguments
     * @throw  BadFunctionCallException if unknown method
     * @throw  FoundationException      if no poolers found
     * @return ClientInterface
     */
    public function __call($method, $arguments)
    {
        if (!preg_match('/get([A-Z][A-Za-z]+)/', $method, $matchs)) {
            throw new \BadFunctionCallException(sprintf("Unknown method 'Session::%s()'.", $method));
        }

        return $this->getClientUsingPooler(
            Inflector::underscore($matchs[1]),
            count($arguments) > 0 ? $arguments[0] : null
        );
    }

    /**
     * getRegisterPoolersNames
     *
     * Useful to test & debug.
     *
     * @access public
     * @return array
     */
    public function getRegisterPoolersNames()
    {
        return array_keys($this->client_poolers);
    }
}
