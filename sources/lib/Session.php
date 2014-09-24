<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\DatabaseConfiguration;
use PommProject\Foundation\Connection;
use PommProject\Foundation\ParameterHolder;
use PommProject\Foundation\Inflector;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Client\ClientHolder;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\QueryManager\SimpleQueryManager;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Exception\SqlException;
use PommProject\Foundation\Exception\ConnectionException;

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
 */
class Session
{
    private   $connection;
    protected $database_configuration;
    protected $client_holder;
    protected $client_poolers = [];
    protected $query_manager;

    /**
     * __construct
     *
     * Constructor.
     * In order to create a physical connection to the database, it requires a
     * 'dsn' parameter from the ParameterHolder.
     *
     * @access public
     * @param  DatabaseConfiguration $configuration
     * @return void
     */
    public function __construct(DatabaseConfiguration $configuration)
    {
        $this->database_configuration = $configuration;
        $this->client_holder          = new ClientHolder();
        $this->connection             = new Connection(
            $this->database_configuration->getParameterHolder()->mustHave('dsn')->getParameter('dsn')
            );
        $this->query_manager          = new SimpleQueryManager();
    }

    /**
     * __destruct
     *
     * Gently shutdown all clients when the Session is getting down prior to
     * connection termination.
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->client_holder->shutdown();
        $this->client_poolers = [];
        $this->connection->close();
    }

    /**
     * getHandler
     *
     * Return the database connection handler.
     *
     * @access public
     * @return resource
     */
    public function getHandler()
    {
        return $this->connection->getHandler();
    }

    /**
     * setQueryManager
     *
     * Replace the current query manager.
     *
     * @access public
     * @param  QueryManagerInterface $query_manager
     * @return Session               $this
     */
    public function setQueryManager(QueryManagerInterface $query_manager)
    {
        $this->query_manager = $query_manager;

        return $this;
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
     * registerClientPooler
     *
     * Add or replace a Client pooler for the specified type.
     *
     * @access public
     * @param  string                $type
     * @param  ClientPoolerInterface $client_pooler
     * @return Session            $this
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
     * @param  string $type
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
                    join(', ', array_keys($this->client_poolers))
                )
            );
        }

        return $this->client_poolers[$type];
    }

    /**
     * getClientUsingPooler
     *
     * Summon a pooler to retrieve a client. If the pooler does not exist, a
     * FoundationException is thrown.
     *
     * @access public
     * @param  string $type
     * @param  string $identifier
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
     * @param  string $method
     * @param  array  $arguments
     * @throw  BadFunctionCallException if unknown method
     * @throw  FoundationException      if no poolers found
     * @return ClientInterface
     */
    public function __call($method, $arguments)
    {
        if (!preg_match('/get([A-Z][A-Za-z]+)/', $method, $matchs)) {
            throw new \BadFunctionCallException(sprintf("Unknown method 'Session::%s()'.", $method));
        }

        return $this->getClientUsingPooler(Inflector::underscore($matchs[1]), $arguments[0]);
    }

    /**
     * getDatabaseConfiguration
     *
     * Returns the connection's database configuration.
     *
     * @access public
     * @return Database
     */
    public function getDatabaseConfiguration()
    {
        return $this->database_configuration;
    }

    /**
     * query
     *
     * Send a query to the query manager.
     *
     * @access public
     * @param  string $sql
     * @param  array  $values
     * @return ResultIterator
     */
    public function query($sql, array $values = [])
    {
        return $this->query_manager->query($sql, $values);
    }

    /**
     * executeAnonymousQuery
     *
     * Performs a raw SQL query
     *
     * @access public
     * @param  string   $sql The sql statement to execute.
     * @return resource
     */
    public function executeAnonymousQuery($sql)
    {
        $ret = @pg_send_query($this->getHandler(), $sql);

        if ($ret === false) {
            throw new ConnectionException(sprintf("Anonymous query « %s » failed.", $sql));
        }

        return $this->getQueryResult($sql);
    }

    /**
     * getQueryResult
     *
     * Get an asynchronous query result.
     *
     * @param  string (default null) the SQL query to make informative error
     * message.
     * @throw  ConnectionException if no response are available.
     * @throw  SqlException if the result is an error.
     * @return resource query result.
     */
    public function getQueryResult($sql = null)
    {
        $result = pg_get_result($this->getHandler());

        if ($result === false) {
            throw new ConnectionException(sprintf("Query result stack is empty."));
        }

        $status = pg_result_status($result, \PGSQL_STATUS_LONG);

        if ($status !== \PGSQL_COMMAND_OK && $status !== \PGSQL_TUPLES_OK) {
            throw new SqlException($result, $sql);
        }

        return $result;
    }

    /**
     * escapeIdentifier
     *
     * Escape database object's names. This is different from value escaping
     * as objects names are surrounded by double quotes. API function does
     * provide a nice escaping with -- hopefully -- UTF8 support.
     *
     * @see http://www.postgresql.org/docs/current/static/sql-syntax-lexical.html
     * @access public
     * @param  string $name The string to be escaped.
     * @return string the escaped string.
     */
    public function escapeIdentifier($name)
    {
        return \pg_escape_identifier($this->getHandler(), $name);
    }

    /**
     * escapeLiteral
     *
     * Escape a text value.
     *
     * @access public
     * @param  string The string to be escaped
     * @return string the escaped string.
     */
    public function escapeLiteral($var)
    {
        return \pg_escape_literal($this->getHandler(), $var);
    }
}
