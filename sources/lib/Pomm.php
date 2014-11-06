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
use PommProject\Foundation\Session\SessionBuilder as VanillaSessionBuilder;
use PommProject\Foundation\Session\Session;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

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
class Pomm implements \ArrayAccess, LoggerAwareInterface
{
    protected $builders = [];
    protected $sessions = [];

    use LoggerAwareTrait;

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
        foreach ($configurations as $name => $configuration) {
            if (isset($configuration['class:session_builder'])) {
                $builder_class = $configuration['class:session_builder'];

                try {
                    $reflection = new \ReflectionClass($builder_class);

                    if (!$reflection->isSubClassOf('\PommProject\Foundation\Session\SessionBuilder')) {
                        throw new FoundationException(
                            sprintf(
                                "Class '%s' is not a subclass of \Pomm\Foundation\Session\SessionBuilder.",
                                $builder_class
                            )
                        );
                    }
                } catch (\ReflectionException $e) {
                    throw new FoundationException(
                        sprintf(
                            "Could not instanciate class '%s'.",
                            $builder_class
                        ),
                        null,
                        $e
                    );
                }
            } else {
                $builder_class = '\PommProject\Foundation\SessionBuilder';
            }

            $this->builders[$name] = new $builder_class($configuration);
        }
    }

    /**
     * addBuilder
     *
     * Add a new session builder. Override any previously existing builder with
     * the same name.
     *
     * @access public
     * @param  string                   $builder_name
     * @param  VanillaSessionBuilder    $builder
     * @return Pomm                     $this
     */
    public function addBuilder($builder_name, VanillaSessionBuilder $builder)
    {
        $this->builders[$builder_name] = $builder;

        return $this;
    }

    /**
     * hasBuilder
     *
     * Return if true or false the given builder exists.
     *
     * @access public
     * @param  string $name
     * @return bool
     */
    public function hasBuilder($name)
    {
        return (bool) (isset($this->builders[$name]));
    }

    /**
     * removeBuilder
     *
     * Remove the builder with the given name.
     *
     * @access public
     * @param  string   $name
     * @throw FoundationException if name does not exist.
     * @return Pomm     $this
     */
    public function removeBuilder($name)
    {
        unset($this->builderMustExist($name)->builders[$name]);

        return $this;
    }

    /**
     * getBuilder
     *
     * Return the given builder.
     *
     * @access public
     * @param  string $name
     * @return VanillaSessionBuilder
     */
    public function getBuilder($name)
    {
        return $this->builderMustExist($name)->builders[$name];
    }

    /**
     * getSession
     *
     * Return a session from the pool. If no session exists, an attempt is made
     * to create one.
     *
     * @access public
     * @param  string  $name
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
     * Create a new session using a session_builder and set it to the pool. Any
     * previous session for this name is overrided.
     *
     * @access public
     * @param  string  $name
     * @throw  FoundationException if builder does not exist.
     * @return Session
     */
    public function createSession($name)
    {
        $this->sessions[$name] = $this
            ->builderMustExist($name)
            ->builders[$name]
            ->buildSession()
            ;

        $session = $this->sessions[$name];

        if ($this->logger !== null) {
            $session->setLogger($this->logger);
        }

        return $session;
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
     * getSessionBuilders
     *
     * Return the builders. This is mainly done for testing
     * purposes.
     *
     * @access public
     * @return array
     */
    public function getSessionBuilders()
    {
        return $this->builders;
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
        $this->addBuilder($offset, $value);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetUnset($offset)
    {
        $this->removeBuilder($offset);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetExists($offset)
    {
        return $this->hasBuilder($offset);
    }

    /**
     * builderMustExist
     *
     * Throw a FoundationException if the given builder does not exist.
     *
     * @access private
     * @param  string   $name
     * @throw  FoundationException
     * @return Pom      $this
     */
    private function builderMustExist($name)
    {
        if (!$this->hasBuilder($name)) {
            throw new FoundationException(
                sprintf(
                    "No such builder '%s'. Available builders are {%s}.",
                    $name,
                    join(
                        ', ',
                        array_map(
                            function ($val) { return sprintf("'%s'", $val); },
                            array_keys($this->getSessionBuilders())
                        )
                    )
                )
            );
        }

        return $this;
    }
}
