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

use PommProject\Foundation\Exception\ConnectionException;
use PommProject\Foundation\Exception\SqlException;

/**
 * Connection
 *
 * Manage connection through a resource handler.
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Connection
{
    const CONNECTION_STATUS_NONE = 0;
    const CONNECTION_STATUS_GOOD = 1;
    const CONNECTION_STATUS_BAD  = 2;

    protected $handler = null;
    protected $parameter_holder;
    protected $configuration = [];

    /**
     * __construct
     *
     * Constructor. Test if the givn DSN is valid.
     *
     * @access public
     * @param  string $dsn
     * @return void
     */
    public function __construct($dsn, array $configuration = [])
    {
        $this->parameter_holder = new ParameterHolder();
        $this->configuration    = $configuration;
        $this->parseDsn($dsn);
    }

    /**
     * close
     *
     * Close the connection if any.
     *
     * @access public
     * @return Connection $this
     */
    public function close()
    {
        if ($this->hasHandler()) {
            pg_close($this->handler);
        }

        return $this;
    }

    /**
     * addConfiguration
     *
     * Add configuration settings. If settings exist, they are overridden.
     *
     * @access public
     * @param  array          $configuration
     * @return Connection $this
     */
    public function addConfiguration(array $configuration)
    {
        $this
            ->checkConnectionUp()
            ->configuration = array_merge($this->configuration, $configuration)
            ;

        return $this;
    }

    /**
     * addConfigurationSetting
     *
     * Add or everride a configuration definition.
     *
     * @access public
     * @param  string         $name
     * @param  string         $value
     * @return Connection
     */
    public function addConfigurationSetting($name, $value)
    {
        $this->checkConnectionUp("Cannot set configuration once a connection is made with the server.")
            ->configuration[$name] = $value;

        return $this;
    }

    /**
     * getConfiguration
     *
     * Return the configuration settings.
     *
     * @access public
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * getHandler
     *
     * Return the connection handler. If no connection are open, it opens one.
     *
     * @access protected
     * @throw  ConnectionException if connection is open in a bad state.
     * @return resource
     */
    protected function getHandler()
    {
        switch ($this->getConnectionStatus()) {
            case static::CONNECTION_STATUS_NONE:
                $this->launch();
                // no break
            case static::CONNECTION_STATUS_GOOD:
                return $this->handler;
            case static::CONNECTION_STATUS_BAD:
                throw new ConnectionException(sprintf("Connection problem. Read your server's log about this, I have no more informations."));
        }
    }

    /**
     * hasHandler
     *
     * Tell if a handler is set or not.
     *
     * @access protected
     * @return bool
     */
    protected function hasHandler()
    {
        return (bool) ($this->handler !== null);
    }

    /**
     * getConnectionStatus
     *
     * Return a connection status.
     *
     * @access public
     * @return int
     */
    public function getConnectionStatus()
    {
        if (!$this->hasHandler()) {
            return static::CONNECTION_STATUS_NONE;
        }

        switch (pg_connection_status($this->handler)) {
            case \PGSQL_CONNECTION_OK:
                return static::CONNECTION_STATUS_GOOD;
            default:
                return static::CONNECTION_STATUS_BAD;
        }
    }

    /**
     * parseDsn()
     *
     * Sets the different parameters from the DSN.
     *
     * @access private
     * @param  string         DSN
     * @return Connection $this
     */
    private function parseDsn($dsn)
    {
        if (!preg_match('#([a-z]+)://([^:@]+)(?::([^@]+))?(?:@([\w\.-]+|!/.+[^/]!)(?::(\w+))?)?/(.+)#', $dsn, $matchs)) {
            throw new ConnectionException(sprintf('Could not parse DSN "%s".', $dsn));
        }

        if ($matchs[1] == null || $matchs[1] !== 'pgsql') {
            throw new ConnectionException(sprintf("bad protocol information '%s' in dsn '%s'. Pomm does only support 'pgsql' for now.", $matchs[1], $dsn));
        }

        $adapter = $matchs[1];

        if ($matchs[2] === null) {
            throw ConnectionException(sprintf('No user information in dsn "%s".', $dsn));
        }

        $user = $matchs[2];
        $pass = $matchs[3];

        if (preg_match('/!(.*)!/', $matchs[4], $host_matchs)) {
            $host = $host_matchs[1];
        } else {
            $host = $matchs[4];
        }

        $port = $matchs[5];

        if ($matchs[6] === null) {
            throw new ConnectionException(sprintf('No database name in dsn "%s".', $dsn));
        }

        $database = $matchs[6];
        $this->parameter_holder
            ->setParameter('adapter',  $adapter)
            ->setParameter('user',     $user)
            ->setParameter('pass',     $pass)
            ->setParameter('host',     $host)
            ->setParameter('port',     $port)
            ->setParameter('database', $database)
            ->mustHave('user')
            ->mustHave('database')
            ;

        return $this;
    }

    /**
     * launch
     *
     * Open a connection on the database.
     *
     * @access private
     * return  Connection $this
     */
    private function launch()
    {
        $connect_parameters = [sprintf("user=%s dbname=%s", $this->parameter_holder['user'], $this->parameter_holder['database'])];

        if ($this->parameter_holder['host'] !== '') {
            $connect_parameters[] = sprintf('host=%s', $this->parameter_holder['host']);
        }

        if ($this->parameter_holder['port'] !== '') {
            $connect_parameters[] = sprintf('port=%s', $this->parameter_holder['port']);
        }

        if ($this->parameter_holder['pass'] !== '') {
            $connect_parameters[] = sprintf('password=%s', addslashes($this->parameter_holder['pass']));
        }

        $handler = pg_connect(join(' ', $connect_parameters), \PGSQL_CONNECT_FORCE_NEW);

        if ($handler === false) {
            throw new ConnectionException(
                sprintf(
                    "Error connecting to the database with parameters '%s'.",
                    join(' ', $connect_parameters)
                ));
        } else {
            $this->handler = $handler;
        }

        if ($this->getConnectionStatus() !== static::CONNECTION_STATUS_GOOD) {
            throw new ConnectionException(
                sprintf("Connection open but in a bad state. Read your database server log to lear more about this.")
            );
        }

        $this->sendConfiguration();

        return $this;
    }

    /**
     * sendConfiguration
     *
     * Send the configuration settings to the server.
     *
     * @access protected
     * @return Connection $this
     */
    protected function sendConfiguration()
    {
        $sql=[];

        foreach ($this->configuration as $setting => $value) {
            $sql[] = sprintf("set %s = %s", pg_escape_identifier($this->handler, $setting), pg_escape_literal($this->handler, $value));
        }

        if (count($sql) > 0) {
            if (pg_query($this->getHandler(), join('; ', $sql)) === false) {
                throw new ConnectionException(sprintf("Error while applying settings '%s'.", join('; ', $sql)));
            }
        }

        return $this;
    }

    /**
     * checkConnectionUp
     *
     * Check if the handler is set and throw an Exception if yes.
     *
     * @access private
     * @param  string         error_message
     * @return Connection $this
     */
    private function checkConnectionUp($error_message = '')
    {
        if ($this->hasHandler()) {
            $error_message == null ? "Connection is already made with the server" : $error_message;

            throw new ConnectionException($error_message);
        }

        return $this;
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

    /**
     * escapeBytea
     *
     * Wrap pg_escape_bytea
     *
     * @access public
     * @param  string $word
     * @return string
     */
    public function escapeBytea($word)
    {
        return pg_escape_bytea($this->getHandler(), $word);
    }

    /**
     * unescapeBytea
     *
     * Unescape postgresql bytea.
     *
     * @access public
     * @param  string $bytea
     * @return string
     */
    public function unescapeBytea($bytea)
    {
        return pg_unescape_bytea($bytea);
    }

    /**
     * sendQueryWithParameters
     *
     * Execute a asynchronous query with parameters and send the results.
     *
     * @access public
     * @param  string $query
     * @param  array $parameters
     * @return resource
     */
    public function sendQueryWithParameters($query, array $parameters = [])
    {
        $query_ok = pg_send_query_params(
            $this->getHandler(),
            $query,
            $parameters
        );

        if ($query_ok === false) {
            throw new ConnectionException(sprintf("Error, could not send query ===\n%s\n===.", $sql));
        }

        return $this->getQueryResult($query);
    }

    /**
     * sendPrepareQuery
     *
     * Send a prepare query statement to the server.
     *
     * @access publci
     * @param  string     $identifier
     * @param  string     $sql
     * @return Connection $this
     */
    public function sendPrepareQuery($identifier, $sql)
    {
        if (pg_send_prepare($this->getHandler(), $identifier, $sql) === false) {
            throw new ConnectionException(sprintf("Could not send prepare statement «%s».", $sql));
        }

        $this->getQueryResult($sql);

        return $this;
    }

    /**
     * sendExecuteQuery
     *
     * Execute a prepared statement.
     *
     * @access public
     * @param  string $identifier
     * @param  array  $parameters
     * @return resource
     */
    public function sendExecuteQuery($identifier, array $parameters = [])
    {
        if (pg_send_execute($this->getHandler(), $identifier, $parameters) === false) {
            throw new ConnectionException(sprintf("Connection error while executing prepared query '%s'.", $identifier));
        }

        return $this->getQueryResult(sprintf("prepared query id = '%s'.", $identifier));
    }

    /**
     * getClientEncoding
     *
     * Return the actual client encoding.
     *
     * @access public
     * @return string
     */
    public function getClientEncoding()
    {
        $encoding = pg_client_encoding($this->getHandler());

        if ($encoding === false) {
            throw new ConnectionException(sprintf("Could not get client encoding."));
        }

        return $encoding;
    }

    /**
     * setClientEncoding
     *
     * Set client encoding.
     *
     * @access public
     * @param  string     $encoding
     * @return Connection $this;
     */
    public function setClientEncoding($encoding)
    {
        $result = pg_set_client_encoding($this->getHandler(), $encoding);

        if ($result === -1) {
            throw new ConnectionException(sprintf("Could not set client encoding '%s'.", $encoding));
        }

        return $this;
    }
}
