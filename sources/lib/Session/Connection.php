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

use PommProject\Foundation\Exception\ConnectionException;
use PommProject\Foundation\Exception\SqlException;
use PommProject\Foundation\Session\ConnectionConfigurator;
use PommProject\Foundation\Session\ResultHandler;

/**
 * Connection
 *
 * Manage connection through a resource handler.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Connection
{
    const CONNECTION_STATUS_NONE    = 0;
    const CONNECTION_STATUS_GOOD    = 1;
    const CONNECTION_STATUS_BAD     = 2;
    const ISOLATION_READ_COMMITTED  = "READ COMMITTED";  // default
    const ISOLATION_READ_REPEATABLE = "READ REPEATABLE"; // from Pg 9.1
    const ISOLATION_SERIALIZABLE    = "SERIALIZABLE";    // changes in 9.1
    const CONSTRAINTS_DEFERRED      = "DEFERRED";
    const CONSTRAINTS_IMMEDIATE     = "IMMEDIATE";       // default
    const ACCESS_MODE_READ_ONLY     = "READ ONLY";
    const ACCESS_MODE_READ_WRITE    = "READ WRITE";      // default

    protected $handler = null;
    protected $configurator;

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
        $this->configurator = new ConnectionConfigurator($dsn);
        $this->configurator->addConfiguration($configuration);
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
     * @param  array               $configuration
     * @throw  ConnectionException if connection is already up.
     * @return Connection          $this
     */
    public function addConfiguration(array $configuration)
    {
        $this
            ->checkConnectionUp("Cannot update configuration once the connection is open.")
            ->configurator->addConfiguration($configuration);

        return $this;
    }

    /**
     * addConfigurationSetting
     *
     * Add or everride a configuration definition.
     *
     * @access public
     * @param  string     $name
     * @param  string     $value
     * @return Connection
     */
    public function addConfigurationSetting($name, $value)
    {
        $this->checkConnectionUp("Cannot set configuration once a connection is made with the server.")
            ->configurator->set($name, $value);

        return $this;
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
     * getTransactionStatus
     *
     * Return the current transaction status.
     * Return a PHP constant.
     * @see http://fr2.php.net/manual/en/function.pg-transaction-status.php
     *
     * @access public
     * @return int
     */
    public function getTransactionStatus()
    {
        return pg_transaction_status($this->handler);
    }

    /**
     * launch
     *
     * Open a connection on the database.
     *
     * @access private
     * @throw  ConnectionException if connection fails.
     * return  Connection $this
     */
    private function launch()
    {
        $string = $this->configurator->getConnectionString();
        $handler = pg_connect($string, \PGSQL_CONNECT_FORCE_NEW);

        if ($handler === false) {
            throw new ConnectionException(
                sprintf(
                    "Error connecting to the database with parameters '%s'.",
                    preg_replace('/password=[^ ]+/', 'password=xxxx', $string)
                )
            );
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

        foreach ($this->configurator->getConfiguration() as $setting => $value) {
            $sql[] = sprintf("set %s = %s", pg_escape_identifier($this->handler, $setting), pg_escape_literal($this->handler, $value));
        }

        if (count($sql) > 0) {
            $this->testQuery(
                pg_query($this->getHandler(), join('; ', $sql)),
                sprintf("Error while applying settings '%s'.", join('; ', $sql))
            );
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
     * @param  string              $sql The sql statement to execute.
     * @return ResultHandler|array
     */
    public function executeAnonymousQuery($sql)
    {
        $ret = pg_send_query($this->getHandler(), $sql);

        return $this
            ->testQuery($ret, sprintf("Anonymous query failed '%s'.", $sql))
            ->getQueryResult($sql)
            ;
    }

    /**
     * getQueryResult
     *
     * Get an asynchronous query result.
     * The only reason for the SQL query to be passed as parameter is to throw
     * a meaningful exception when an error is raised.
     * Since it is possible to send several queries at a time, This method can
     * return an array of ResultHandler.
     *
     * @access protected
     * @param  string (default null)
     * @throw  ConnectionException if no response are available.
     * @throw  SqlException if the result is an error.
     * @return ResultHandler|array
     */
    protected function getQueryResult($sql = null)
    {
        $results = [];

        while ($result = pg_get_result($this->getHandler())) {
            $status = pg_result_status($result, \PGSQL_STATUS_LONG);

            if ($status !== \PGSQL_COMMAND_OK && $status !== \PGSQL_TUPLES_OK) {
                throw new SqlException($result, $sql);
            }

            $results[] = new ResultHandler($result);
        }

        if (count($results) === 0) {
            throw new ConnectionException(
                sprintf(
                    "There are no waiting results in connection.\nQuery = '%s'.",
                    $sql
                )
            );
        }

        return count($results) === 1 ? $results[0] : $results;
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
     * @param  string $string The string to be escaped.
     * @return string the escaped string.
     */
    public function escapeIdentifier($string)
    {
        return \pg_escape_identifier($this->getHandler(), $string);
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
    public function escapeLiteral($string)
    {
        return \pg_escape_literal($this->getHandler(), $string);
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
     * @param  string        $query
     * @param  array         $parameters
     * @return ResultHandler query result wrapper
     */
    public function sendQueryWithParameters($query, array $parameters = [])
    {
        $res = pg_send_query_params(
            $this->getHandler(),
            $query,
            $parameters
        );

        try {
            return $this
                ->testQuery($res, $query)
                ->getQueryResult($query)
                ;
        } catch (SqlException $e) {
            throw new ConnectionException(
                sprintf(
                    "Parameters = {%s}.",
                    print_r($parameters, true)
                ),
                null,
                $e
            );
        }
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
        $this
            ->testQuery(
                pg_send_prepare($this->getHandler(), $identifier, $sql),
                sprintf("Could not send prepare statement «%s».", $sql)
            )
            ->getQueryResult(sprintf("PREPARE ===\n%s\n ===", $sql))
            ;

        return $this;
    }

    /**
     * testQueryAndGetResult
     *
     * Factor method to test query return and summon getQueryResult().
     *
     * @access protected
     * @param  mixed      $query_return
     * @param  string     $sql
     * @return Connection $this
     */
    protected function testQuery($query_return, $sql)
    {
        if ($query_return === false) {
            throw new ConnectionException(sprintf("Query Error : '%s'.", $sql));
        }

        return $this;
    }
    /**
     * sendExecuteQuery
     *
     * Execute a prepared statement.
     * The optional SQL parameter is for debuging purpose only.
     *
     * @access public
     * @param  string        $identifier
     * @param  array         $parameters
     * @param  string        $sql
     * @return ResultHandler
     */
    public function sendExecuteQuery($identifier, array $parameters = [], $sql = '')
    {
        $ret = pg_send_execute($this->getHandler(), $identifier, $parameters);

        return $this
            ->testQuery($ret, sprintf("Prepared query '%s'.", $identifier))
            ->getQueryResult(sprintf("EXECUTE ===\n%s\n ===\nparameters = {%s}", $sql, join(', ', $parameters)))
            ;
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
        $this->testQuery($encoding, 'get client encoding');

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

        return $this
            ->testQuery((bool) ($result != -1), sprintf("Set client encoding to '%s'.", $encoding))
            ;
    }

    /**
     * getNotification
     *
     * Get pending notifications. If no notifications are waiting, NULL is
     * returned. Otherwise an associative array containing the optional data
     * and de backend's PID is returned.
     *
     * @access public
     * @return array|null
     */
    public function getNotification()
    {
        $data = pg_get_notify($this->handler, \PGSQL_ASSOC);

        return $data === false ? null : $data;
    }
}
