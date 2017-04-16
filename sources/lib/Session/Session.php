<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package     Foundation
 * @copyright   2014 - 2017 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see         LoggerAwareInterface
 */
class Session implements LoggerAwareInterface
{
    protected $connection;
    protected $client_holder;
    protected $client_poolers = [];
    protected $stamp;
    protected $is_shutdown = false;

    use LoggerAwareTrait;

    /**
     * __construct
     *
     * Constructor.
     * In order to create a physical connection to the database, it requires a
     * 'dsn' parameter from the ParameterHolder.
     *
     * @param   Connection       $connection
     * @param   ClientHolder     $client_holder
     * @param   string           $stamp
     */
    public function __construct(
        Connection      $connection,
        ClientHolder    $client_holder = null,
        $stamp = null
    ) {
        $this->connection    = $connection;
        $this->client_holder = $client_holder === null
            ? new ClientHolder
            : $client_holder
            ;
        $this->stamp = $stamp;
    }

    /**
     * __destruct
     *
     * A short description here
     *
     * @return null
     */
    public function __destruct()
    {
        if (!$this->is_shutdown) {
            $this->shutdown();
        }
    }

    /**
     * shutdown
     *
     * Gently shutdown all clients when the Session is getting down prior to
     * connection termination.
     *
     * @return  null
     */
    public function shutdown()
    {
        $exceptions = $this->client_holder->shutdown();

        if ($this->hasLogger()) {
            foreach ($exceptions as $exception) {
                printf(
                    "Exception caught during shutdown: %s\n",
                    (string) $exception
                );
            }

            $this->logger = null;
        }

        $this->client_poolers = [];
        $this->connection->close();
        $this->is_shutdown = true;
    }

    /**
     * getStamp
     *
     * Return the session's stamp if any
     *
     * @return  string|null
     */
    public function getStamp()
    {
        return $this->stamp === null ? null : (string) $this->stamp;
    }

    /**
     * getLogger
     *
     * Return the logger if any.
     *
     * @return  LoggerInterface|null
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
     * @return  bool
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
     * @return  Connection
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
     * @param   ClientInterface $client
     * @return  Session $this
     */
    public function registerClient(ClientInterface $client)
    {
        $client->initialize($this);
        $this->client_holder->add($client);
        $this->hasLogger() && $this->getLogger()->debug(
            "Pomm: Registering new client",
            [
                'type' => $client->getClientType(),
                'identifier' => $client->getClientIdentifier(),
            ]
        );

        return $this;
    }

    /**
     * getClient
     *
     * Return a Client from its type and identifier.
     *
     * @param   string $type
     * @param   string $identifier
     * @return  Client or null if not found.
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
     * @param   string $type
     * @param   string $name
     * @return  bool
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
     * @param   ClientPoolerInterface $client_pooler
     * @return  Session               $this
     */
    public function registerClientPooler(ClientPoolerInterface $client_pooler)
    {
        if ($client_pooler->getPoolerType() == null) {
            throw new \InvalidArgumentException("Can not register a pooler for the empty type.");
        }

        $client_pooler->register($this);
        $this->client_poolers[$client_pooler->getPoolerType()] = $client_pooler;
        $this->hasLogger()
            && $this->getLogger()
            ->debug(
                "Pomm: Registering new client pooler.",
                ['type' => $client_pooler->getPoolerType()]
            );

        return $this;
    }

    /**
     * hasPoolerForType
     *
     * Tell if a pooler exist or not.
     *
     * @param   string  $type
     * @return  bool
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
     * @param   string              $type
     * @throws  FoundationException if pooler does not exist
     * @return  ClientPoolerInterface
     */
    public function getPoolerForType($type)
    {
        if (!$this->hasPoolerForType($type)) {
            $error_message = <<<ERROR
No pooler registered for type '%s'. Poolers available: {%s}.
If the pooler you are asking for is not listed there, maybe you have not used
the correct session builder. Use the "class:session_builder" parameter in the
configuration to associate each session with a session builder. A good practice
is to define your own project's session builders.
ERROR;
            if ($this->is_shutdown) {
                $error_message = 'There are no poolers in the session because it is shutdown.';
            }

            throw new FoundationException(
                sprintf(
                    $error_message,
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
     * @param   string $type
     * @return  ClientInterface
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
     * @param   string          $type
     * @param   string          $identifier
     * @return  ClientInterface
     */
    public function getClientUsingPooler($type, $identifier)
    {
        return $this->getPoolerForType($type)->getClient($identifier);
    }

    /**
     * Get query manager shortcut
     *
     * @param   string                   $identifier
     * @throws  FoundationException      if no poolers found
     * @return  \PommProject\Foundation\QueryManager\QueryManagerClient
     */
    public function getQueryManager($identifier = null)
    {
        return $this->getClientUsingPooler('query_manager', $identifier);
    }

    /**
     * Get prepared query manager shortcut
     *
     * @param   string $identifier
     * @throws  FoundationException      if no poolers found
     * @return  \PommProject\Foundation\PreparedQuery\PreparedQueryManager
     */
    public function getPreparedQueryManager($identifier = null)
    {
        return $this->getClientUsingPooler('prepared_query', $identifier);
    }

    /**
     * Get model shortcut
     *
     * Without the pomm-project/model-manage package or if the class name
     * does not exist, this method raise a FoundationException.
     *
     * @param   string                   $className model class name
     * @throws  FoundationException      if no poolers found
     * @return  \PommProject\ModelManager\Model\Model
     */
    public function getModel($className)
    {
        return $this->getClientUsingPooler('model', $className);
    }

    /**
     * __call
     *
     * Create handy methods to access clients through a pooler.
     *
     * @param   string                   $method
     * @param   array                    $arguments
     * @throws  \BadFunctionCallException if unknown method
     * @throws  FoundationException      if no poolers found
     * @return  ClientInterface
     */
    public function __call($method, $arguments)
    {
        if (!preg_match('/get([A-Z][A-Za-z]+)/', $method, $matches)) {
            throw new \BadFunctionCallException(sprintf("Unknown method 'Session::%s()'.", $method));
        }

        return $this->getClientUsingPooler(
            Inflector::underscore($matches[1]),
            count($arguments) > 0 ? $arguments[0] : null
        );
    }

    /**
     * getRegisterPoolersNames
     *
     * Useful to test & debug.
     *
     * @return  array
     */
    public function getRegisterPoolersNames()
    {
        return array_keys($this->client_poolers);
    }
}
