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
use PommProject\Foundation\Session\Session as BaseSession;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Pomm
 *
 * The Pomm service manager.
 *
 * @package     Foundation
 * @copyright   2014 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Pomm implements \ArrayAccess, LoggerAwareInterface
{
    protected $builders = [];
    protected $post_configurations = [];
    protected $sessions = [];
    protected $default;

    use LoggerAwareTrait;

    /**
     * __construct
     *
     * Instantiate a new Pomm Service class. It takes an array of
     * configurations as parameter. Following configuration settings are
     * supported by this service for each configuration:
     *
     * class_name   name of the DatabaseConfiguration class to instantiate.
     *
     * @access  public
     * @param   array $configurations
     */
    public function __construct(array $configurations = [])
    {
        foreach ($configurations as $name => $configuration) {
            $builder_class = '\PommProject\Foundation\SessionBuilder';
            if (isset($configuration['class:session_builder'])) {
                $builder_class = $this->checkSessionBuilderClass($configuration['class:session_builder']);
            }

            $this->addBuilder($name, new $builder_class($configuration));

            if (isset($configuration['pomm:default']) && $configuration['pomm:default'] === true) {
                $this->setDefaultBuilder($name);
            }
        }
    }

    /**
     * checkSessionBuilderClass
     *
     * Check if the given builder class is valid.
     *
     * @access  private
     * @param   string              $builder_class
     * @throws  FoundationException if not valid
     * @return  string              $builder_class
     */
    private function checkSessionBuilderClass($builder_class)
    {
        try {
            $reflection = new \ReflectionClass($builder_class);

            if (!$reflection->isSubclassOf('\PommProject\Foundation\Session\SessionBuilder')) {
                throw new FoundationException(
                    sprintf(
                        "Class '%s' is not a subclass of \PommProject\Foundation\Session\SessionBuilder.",
                        $builder_class
                    )
                );
            }
        } catch (\ReflectionException $e) {
            throw new FoundationException(
                sprintf(
                    "Could not instantiate class '%s'.",
                    $builder_class
                ),
                null,
                $e
            );
        }

        return $builder_class;
    }

    /**
     * setDefaultBuilder
     *
     * Set the name for the default session builder.
     *
     * @access  public
     * @param   string  $name
     * @return  Pomm    $this
     * @throws FoundationException
     */
    public function setDefaultBuilder($name)
    {
        if (!$this->hasBuilder($name)) {
            throw new FoundationException(
                sprintf(
                    "No such builder '%s'.",
                    $name
                )
            );
        }

        $this->default = $name;

        return $this;
    }

    /**
     * getDefaultSession
     *
     * Return a session built by the default session builder.
     *
     * @access  public
     * @return  BaseSession
     * @throws FoundationException
     */
    public function getDefaultSession()
    {
        if ($this->default === null) {
            throw new FoundationException(
                "No default session builder set."
            )
            ;
        }

        return $this->getSession($this->default);
    }

    /**
     * isDefaultSession
     *
     * Check if $name is a default session builder
     *
     * @param   string $name
     * @return  bool
     */
    public function isDefaultSession($name)
    {
        return (bool) ($this->default == $name);
    }

    /**
     * addBuilder
     *
     * Add a new session builder. Override any previously existing builder with
     * the same name.
     *
     * @access  public
     * @param   string                   $builder_name
     * @param   VanillaSessionBuilder    $builder
     * @return  Pomm                     $this
     */
    public function addBuilder($builder_name, VanillaSessionBuilder $builder)
    {
        $this->builders[$builder_name] = $builder;
        $this->post_configurations[$builder_name] = [];

        if ($this->default === null) {
            $this->setDefaultBuilder($builder_name);
        }

        return $this;
    }

    /**
     * addPostConfiguration
     *
     * Add a environment dependent post configuration callable that will be run
     * once after the session creation.
     *
     * @access  public
     * @param   string   $name
     * @param   callable $callable
     * @return  Pomm     $this
     */
    public function addPostConfiguration($name, callable $callable)
    {
        $this
            ->builderMustExist($name)
            ->post_configurations[$name][] = $callable
            ;

        return $this;
    }

    /**
     * hasBuilder
     *
     * Return if true or false the given builder exists.
     *
     * @access  public
     * @param   string $name
     * @return  bool
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
     * @access  public
     * @param   string   $name
     * @throws  FoundationException if name does not exist.
     * @return  Pomm     $this
     */
    public function removeBuilder($name)
    {
        unset(
            $this->builderMustExist($name)->builders[$name],
            $this->post_configurations[$name]
        );

        return $this;
    }

    /**
     * getBuilder
     *
     * Return the given builder.
     *
     * @access  public
     * @param   string $name
     * @return  VanillaSessionBuilder
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
     * @access  public
     * @param   string  $name
     * @return  BaseSession
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
     * @access  public
     * @param   string  $name
     * @throws  FoundationException if builder does not exist.
     * @return  BaseSession
     */
    public function createSession($name)
    {
        $this->sessions[$name] = $this
            ->builderMustExist($name)
            ->builders[$name]
            ->buildSession($name)
            ;

        $session = $this->sessions[$name];

        foreach ($this->post_configurations[$name] as $callable) {
            call_user_func($callable, $session);
        }

        if ($this->logger !== null) {
            $session->setLogger($this->logger);
        }

        return $session;
    }

    /**
     * hasSession
     *
     * Does a given session exist in the pool ?
     *
     * @access  public
     * @param   string $name
     * @return  bool
     */
    public function hasSession($name)
    {
        return (bool) isset($this->sessions[$name]);
    }

    /**
     * removeSession
     *
     * Remove a session from the pool if it exists.
     *
     * @access  public
     * @param   string              $name
     * @throws  FoundationException if no builders with that name exist
     * @return  Pomm                $this
     */
    public function removeSession($name)
    {
        if ($this->builderMustExist($name)->hasSession($name)) {
            unset($this->sessions[$name]);
        }

        return $this;
    }

    /**
     * getSessionBuilders
     *
     * Return the builders. This is mainly done for testing
     * purposes.
     *
     * @access  public
     * @return  array
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
     * shutdown
     *
     * Shutdown and remove sessions from the service. If no arguments are
     * given, all the instantiated sessions are shutdown. Otherwise, only given
     * sessions are shutdown.
     *
     * @access public
     * @param  array $session_names
     * @return Pomm  $this
     */
    public function shutdown(array $session_names = [])
    {
        if (empty($session_names)) {
            $sessions = array_keys($this->sessions);
        } else {
            array_map(function ($name) { $this->builderMustExist($name); }, $session_names);
            $sessions = array_intersect(
                array_keys($this->sessions),
                $session_names
            );
        }

        foreach ($sessions as $session_name) {
            $this->getSession($session_name)->shutdown();
            $this->removeSession($session_name);
        }

        return $this;
    }

    /**
     * builderMustExist
     *
     * Throw a FoundationException if the given builder does not exist.
     *
     * @access  private
     * @param   string   $name
     * @throws  FoundationException
     * @return  Pomm      $this
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
