<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @copyright   2014 - 2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see         LoggerAwareInterface
 */
class Session implements LoggerAwareInterface
{
    protected ClientHolder $client_holder;
    protected array $client_poolers = [];
    protected bool $is_shutdown = false;

    use LoggerAwareTrait;

    /**
     * __construct
     *
     * Constructor.
     * In order to create a physical connection to the database, it requires a
     * 'dsn' parameter from the ParameterHolder.
     *
     * @access  public
     * @param Connection $connection
     * @param ClientHolder|null $client_holder
     * @param string|null $stamp
     */
    public function __construct(
        protected Connection $connection,
        ?ClientHolder $client_holder = null,
        protected ?string $stamp = null
    ) {
        $this->client_holder = $client_holder ?? new ClientHolder;
    }

    /**
     * __destruct
     *
     * A short description here
     *
     * @access public
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
     * @access  public
     * @return void
     */
    public function shutdown(): void
    {
        $exceptions = $this->client_holder->shutdown();

        if ($this->hasLogger()) {
            foreach ($exceptions as $exception) {
                printf(
                    "Exception caught during shutdown: %s\n",
                    (string)$exception
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
     * @access  public
     * @return  string|null
     */
    public function getStamp(): ?string
    {
        return $this->stamp === null ? null : (string)$this->stamp;
    }

    /**
     * getLogger
     *
     * Return the logger if any.
     *
     * @access  public
     * @return  LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * hasLogger
     *
     * Return true if a logger is set.
     *
     * @access  public
     * @return  bool
     */
    public function hasLogger(): bool
    {
        return $this->logger !== null;
    }

    /**
     * getConnection
     *
     * Return the database connection.
     *
     * @access  public
     * @return  Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * registerClient
     *
     * Initialize a connection client with context and register it in the
     * client holder.
     *
     * @access  public
     * @param ClientInterface $client
     * @return  Session $this
     */
    public function registerClient(ClientInterface $client): Session
    {
        $client->initialize($this);
        $this->client_holder->add($client);

        if ($this->hasLogger()) {
            $this->getLogger()->debug(
                "Pomm: Registering new client",
                [
                    'type' => $client->getClientType(),
                    'identifier' => $client->getClientIdentifier(),
                ]
            );
        }


        return $this;
    }

    /**
     * getClient
     *
     * Return a Client from its type and identifier.
     *
     * @access  public
     * @param string|null $type
     * @param string $identifier
     * @return ClientInterface|null or null if not found.
     */
    public function getClient(?string $type, string $identifier): ?ClientInterface
    {
        return $this->client_holder->get($type, $identifier);
    }

    /**
     * hasClient
     *
     * Tell if a client exist or not.
     *
     * @access  public
     * @param string $type
     * @param string $name
     * @return  bool
     */
    public function hasClient(string $type, string $name): bool
    {
        return $this->client_holder->has($type, $name);
    }

    /**
     * registerClientPooler
     *
     * Add or replace a Client pooler for the specified type.
     *
     * @access  public
     * @param ClientPoolerInterface $client_pooler
     * @return  Session               $this
     */
    public function registerClientPooler(ClientPoolerInterface $client_pooler): Session
    {
        if ($client_pooler->getPoolerType() == null) {
            throw new \InvalidArgumentException("Can not register a pooler for the empty type.");
        }

        $client_pooler->register($this);
        $this->client_poolers[$client_pooler->getPoolerType()] = $client_pooler;

        if ($this->hasLogger()) {
            $this->getLogger()
                ->debug(
                    "Pomm: Registering new client pooler.",
                    ['type' => $client_pooler->getPoolerType()]
                );
        }

        return $this;
    }

    /**
     * hasPoolerForType
     *
     * Tell if a pooler exist or not.
     *
     * @access  public
     * @param string $type
     * @return  bool
     */
    public function hasPoolerForType(string $type): bool
    {
        return isset($this->client_poolers[$type]);
    }

    /**
     * getPoolerForType
     *
     * Get the registered for the given type.
     *
     * @access  public
     * @param string $type
     * @return  ClientPoolerInterface
     * @throws  FoundationException if pooler does not exist
     */
    public function getPoolerForType(string $type): ClientPoolerInterface
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
     * @access  public
     * @param string $type
     * @return  array
     */
    public function getAllClientForType(string $type): array
    {
        return $this->client_holder->getAllFor($type);
    }

    /**
     * getClientUsingPooler
     *
     * Summon a pooler to retrieve a client. If the pooler does not exist, a
     * FoundationException is thrown.
     *
     * @access  public
     * @param string $type
     * @param string|null $identifier
     * @return  ClientInterface
     * @throws FoundationException
     */
    public function getClientUsingPooler(string $type, ?string $identifier): ClientInterface
    {
        return $this->getPoolerForType($type)->getClient($identifier);
    }

    /**
     * __call
     *
     * Create handy methods to access clients through a pooler.
     *
     * @access  public
     * @param string $method
     * @param array $arguments
     * @return  ClientInterface
     * @throws  FoundationException      if no poolers found
     * @throws  \BadFunctionCallException if unknown method
     */
    public function __call(string $method, array $arguments)
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
     * @access  public
     * @return  array
     */
    public function getRegisterPoolersNames(): array
    {
        return array_keys($this->client_poolers);
    }
}
