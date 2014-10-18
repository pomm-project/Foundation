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

use PommProject\Foundation\ParameterHolder;
use PommProject\Foundation\Exception\ConnectionException;

/**
 * ConnectionConfigurator
 *
 * This class is responsible of configuring the connection.
 * It has to extract informations from the DSN and ensure required arguments
 * are present.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ConnectionConfigurator
{
    protected $configuration;

    /**
     * __construct
     *
     * Initialize configuration.
     *
     * @access public
     * @param  array $configuration
     * @return null
     */
    public function __construct($dsn)
    {
        $this->configuration = new ParameterHolder(
            [
                'dsn' => $dsn,
                'configuration' => $this->getDefaultConfiguration(),
            ]
        );
        $this->parseDsn();
    }

    /**
     * addConfiguration
     *
     * Add configuration settings. If settings exist, they are overridden.
     *
     * @access public
     * @param  array      $configuration
     * @return Connection $this
     */
    public function addConfiguration(array $configuration)
    {
        $this ->configuration->setParameter(
            'configuration',
            array_merge(
                $this->configuration->getParameter('configuration'),
                $configuration
            )
        );

        return $this;
    }

    /**
     * set
     *
     * Set a new configuration setting.
     *
     * @access public
     * @param  string $name
     * @param  mixed $value
     * @return ConnectionConfigurator $this
     */
    public function set($name, $value)
    {
        $configuration = $this->configuration->getParameter('configuration');
        $configuration[$name] = $value;
        $this
            ->configuration
            ->setParameter(
                'configuration',
                $configuration
            );

        return $this;
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
    private function parseDsn()
    {
        $dsn = $this->configuration->mustHave('dsn')->getParameter('dsn');
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
        $this->configuration
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
     * getConnectionString
     *
     * Return the connection string.
     *
     * @access public
     * @return string
     */
    public function getConnectionString()
    {
        $this->parseDsn();
        $connect_parameters = [sprintf("user=%s dbname=%s", $this->configuration['user'], $this->configuration['database'])];

        if ($this->configuration['host'] !== '') {
            $connect_parameters[] = sprintf('host=%s', $this->configuration['host']);
        }

        if ($this->configuration['port'] !== '') {
            $connect_parameters[] = sprintf('port=%s', $this->configuration['port']);
        }

        if ($this->configuration['pass'] !== '') {
            $connect_parameters[] = sprintf('password=%s', addslashes($this->configuration['pass']));
        }

        return join(' ', $connect_parameters);
    }

    /**
     * getDefaultConfiguration
     *
     * Standalone, default configuration.
     *
     * @access protected
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [];
    }

    /**
     * getConfiguration
     *
     * Return current configuration settings.
     *
     * @access public
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration->getParameter('configuration');
    }
}
