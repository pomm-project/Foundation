<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Exception\FoundationException;

/**
 * Pomm
 *
 * The Pomm service manager.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Pomm implements \ArrayAccess
{
    protected $configurations = [];
    protected $sessions       = [];

    /**
     * __construct
     *
     * Instanciate a new Pomm Service class. It takes an array of
     * configurations as parameter. Following configurations settings are
     * supported by this service for each configuration:
     *
     * class_name   name of the DatabaseConfiguration class to instanciate.
     *
     * @access public
     * @param  array $configurations
     * @return void
     */
    public function __construct(array $configurations = [])
    {
        $this->configurations = $configurations;
    }

    /**
     * setConfiguration
     *
     * Add or replace a database configuration.
     *
     * @access public
     * @param  DatabaseConfiguration $configuration
     * @return Pomm                  $this
     */
    public function setConfiguration($name, DatabaseConfiguration $configuration)
    {
        $this->configurations[$name] = $configuration;

        return $this;
    }

    /**
     * getConfiguration
     *
     * Get a configuration.
     *
     * @access public
     * @param  string $name
     * @return DatabaseConfiguration
     */
    public function getConfiguration($name)
    {
        return $this
            ->checkExistConfiguration($name)
            ->expandConfiguration($name)
            ->configurations[$name];
    }

    /**
     * getSession
     *
     * Return a session from the pool. If no session exists, an attempt is made
     * to create one.
     *
     * @access public
     * @param  string $name
     * @return Session
     */
    public function getSession($name)
    {
        if (!$this->hasSession($name)) {
            $this->createSession($name);
        }

        return $this->sessions[$name];
    }

    /**
     * createSession
     *
     * Create a new session using a configuration and set it to the pool. Any
     * previous session for this name is overrided.
     *
     * @access public
     * @param  string $name
     * @return Session
     */
    public function createSession($name)
    {
        $class_name = $this
            ->checkExistConfiguration($name)
            ->expandConfiguration($name)
            ->configurations[$name]
            ->getParameterHolder()
            ->getParameter('session_class_name', '\PommProject\Foundation\Session');

        $this
            ->checkSubClassOf($class_name, '\PommProject\Foundation\Session')
            ->sessions[$name] = new $class_name($this->getConfiguration($name))
            ;

        return $this;
    }

    /**
     * hasConfiguration
     *
     * Does a given configuration exist ?
     *
     * @access public
     * @param  string $name
     * @return bool
     */
    public function hasConfiguration($name)
    {
        return (bool) (isset($this->configurations[$name]));
    }

    /**
     * hasSession
     *
     * Does a given session exists in the pool ?
     *
     * @access public
     * @param  string $name
     * @return bool
     */
    public function hasSession($name)
    {
        return (bool) isset($this->sessions[$name]);
    }

    /**
     * checkExistConfiguration
     *
     * Throw an exception if a given configuration does not exist.
     *
     * @access public
     * @param  string $name
     * throw   FoundationException if exists.
     * @return Pomm   $this
     */
    public function checkExistConfiguration($name)
    {
        if (!$this->hasConfiguration($name)) {
            throw new FoundationException(sprintf("Database configuration '%s' not found.", $name));
        }

        return $this;
    }

    /**
     * clearConfiguration
     *
     * Remove a configuration definition.
     *
     * @access public
     * @param  string $name
     * @return Pomm   $this
     */
    public function clearConfiguration($name)
    {
        unset($this->checkExistConfiguration($name)->configurations[$name]);

        return $this;
    }

    /**
     * getConfigurations
     *
     * Return the configuration holder. This is mainly done for testing
     * purposes.
     *
     * @access public
     * @return array
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @see ArrayAccess
     */
    public function offsetGet($offset)
    {
        return $this->getSession($offset);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        $this->setConfiguration($value);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetUnset($offset)
    {
        $this->clearConfiguration($offset);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetExists($offset)
    {
        return $this->hasConfiguration($offset);
    }

    /**
     * expandConfiguration
     *
     * Ensure a given configuration is a DatabaseConfiguration instance. If
     * not, it spawns one.
     *
     * @access private
     * @param  string $name
     * @return Pomm   $this
     */
    private function expandConfiguration($name)
    {
        if (!is_object($this->configurations[$name])) {
            $this->configurations[$name] = $this->buildDatabaseConfiguration($this->configurations[$name]);
        }

        return $this;
    }

    /**
     * buildDatabaseConfiguration
     *
     * Create a DatabaseConfiguration instance from configuration definition.
     *
     * @access private
     * @param  array  $configuration
     * @return DatabaseConfiguration
     */
    private function buildDatabaseConfiguration(array $configuration)
    {
        if (!isset($configuration['config_class_name'])) {
            return new DatabaseConfiguration($configuration);
        }

        $class_name = $configuration['config_class_name'];
        $this->checkSubClassOf($class_name, '\PommProject\Foundation\DatabaseConfiguration');

        return new $class_name($configuration);

    }

    /**
     * checkSubClassOf
     *
     * Check if a class exists and if it is a subclass of another.
     *
     * @access private
     * @param  string $class
     * @param  string $super_class
     * @return Pomm   $this
     */
    private function checkSubClassOf($class, $super_class)
    {
        try {
            $reflection = new \ReflectionClass($class);

            if (!(trim($class, "\\") === trim($super_class, "\\") || $reflection->isSubClassOf($super_class))) {
                throw new FoundationException(sprintf("Class '%s' must extend '%s'.", $reflection->getName(), $super_class));
            }
        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Class '%s' could not be loaded. Reason given:\n%s", $class, $e->getMessage()));
        }

        return $this;
    }
}
