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
    protected $session_class_name = '\PommProject\Foundation\Session';

    /**
     * __construct
     *
     * Instanciate a new Pomm Service class. It takes an array of configuration
     * as parameter. Following configurations settings are supported by this
     * service:
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

    public function setSessionClassName($class_name)
    {
        try {
            $reflection = new \ReflectionClass($class_name);

            if (!$reflection->isSubClassOf('\PommProject\Foundation\Session')) {
                throw new FoundationException(sprintf("Class '%s' must extend Session", $reflection->getName()));
            }

            $this->session_class_name = $class_name;
        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Invalid class name '%s'. Reason:\n%s", $class_name, $e->getMessage()));
        }

        return $this;
    }

    public function setConfiguration(DatabaseConfiguration $configuration)
    {
        $this->configurations[$configuration->getName()] = $configuration;

        return $this;
    }

    public function getConfiguration($name)
    {
        return $this
            ->checkExistConfiguration($name)
            ->expandConfiguration($name)
            ->configurations[$name];
    }

    public function getSession($name)
    {
        if (!$this->hasSession($name)) {
            $this->createSession($name);
        }

        return $this->sessions[$name];
    }

    public function createSession($name)
    {
        $this->sessions[$name] = new Session($this->getConfiguration($name));

        return $this;
    }

    public function hasConfiguration($name)
    {
        return (bool) (isset($this->configurations[$name]));
    }

    public function hasSession($name)
    {
        return (bool) isset($this->sessions[$name]);
    }

    public function checkExistConfiguration($name)
    {
        if (!$this->hasConfiguration($name)) {
            throw new FoundationException(sprintf("Database configuration '%s' not found.", $name));
        }

        return $this;
    }

    public function clear($name)
    {
        unset($this->checkExistConfiguration($name)->configurations[$name]);

        return $this;
    }

    public function getConfigurations()
    {
        return $this->configurations;
    }

    public function offsetGet($offset)
    {
        return $this->getSession($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($values);
    }

    public function offsetUnset($offset)
    {
        $this->clear($offset);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    private function expandConfiguration($name)
    {
        if (!is_object($this->configurations[$name])) {
            $this->configurations[$name] = $this->buildDatabaseConfiguration($name, $this->configurations[$name]);
        }

        return $this;
    }

    private function buildDatabaseConfiguration($name, $configuration)
    {
        if (!isset($configuration['class_name'])) {
            return new DatabaseConfiguration($name, $configuration);
        }

        $class_name = $configuration['class_name'];

        try {
            $reflection = new \ReflectionClass($class_name);

            if (!$reflection->isSubClassOf('\PommProject\Foundation\DatabaseConfiguration')) {
                throw new FoundationException(sprintf("Class '%s' must extend DatabaseConfiguration", $reflection->getName()));
            }

            return new $class_name($name, $configuration);

        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Class '%s' could not be loaded. Reason given:\n%s", $class_name, $e->getMessage()));
        }
    }
}
