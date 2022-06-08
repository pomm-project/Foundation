<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @copyright   2014 - 2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Pomm implements \ArrayAccess, LoggerAwareInterface
{
    protected array $builders = [];
    protected array $post_configurations = [];
    protected array $sessions = [];
    protected ?string $default = null;

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
     * @throws FoundationException
     */
    public function __construct(array $configurations = [])
    {
        foreach ($configurations as $name => $configuration) {
            $builder_class = \PommProject\Foundation\SessionBuilder::class;
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
     * @throws  FoundationException if not valid
     */
    private function checkSessionBuilderClass(string $builder_class): string
    {
        try {
            $reflection = new \ReflectionClass($builder_class);

            if (!$reflection->isSubclassOf(VanillaSessionBuilder::class)) {
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
                0,
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
     * @throws FoundationException
     */
    public function setDefaultBuilder(string $name): Pomm
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
     * @throws FoundationException
     */
    public function getDefaultSession(): BaseSession
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
     */
    public function isDefaultSession(string $name): bool
    {
        return $this->default == $name;
    }

    /**
     * addBuilder
     *
     * Add a new session builder. Override any previously existing builder with
     * the same name.
     *
     * @access  public
     * @throws FoundationException
     */
    public function addBuilder(string $builder_name, VanillaSessionBuilder $builder): Pomm
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
     * Add an environment dependent post configuration callable that will be run
     * once after the session creation.
     *
     * @access  public
     * @throws FoundationException
     */
    public function addPostConfiguration(string $name, callable $callable): Pomm
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
     */
    public function hasBuilder(string $name): bool
    {
        return isset($this->builders[$name]);
    }

    /**
     * removeBuilder
     *
     * Remove the builder with the given name.
     *
     * @access  public
     * @throws  FoundationException if name does not exist.
     */
    public function removeBuilder(string $name): Pomm
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
     * @throws FoundationException
     */
    public function getBuilder(string $name): VanillaSessionBuilder
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
     * @throws FoundationException
     */
    public function getSession(string $name): BaseSession
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
     * @throws  FoundationException if builder does not exist.
     */
    public function createSession(string $name): BaseSession
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
     * @param string $name
     * @return  bool
     */
    public function hasSession(string $name): bool
    {
        return isset($this->sessions[$name]);
    }

    /**
     * removeSession
     *
     * Remove a session from the pool if it exists.
     *
     * @access  public
     * @throws  FoundationException if no builders with that name exist
     */
    public function removeSession(string $name): Pomm
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
     */
    public function getSessionBuilders(): array
    {
        return $this->builders;
    }

    /**
     * @see ArrayAccess
     */
    public function offsetGet(mixed $offset): BaseSession
    {
        return $this->getSession($offset);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addBuilder($offset, $value);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeBuilder($offset);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetExists(mixed $offset): bool
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
     * @throws FoundationException
     */
    public function shutdown(array $session_names = []): Pomm
    {
        if (empty($session_names)) {
            $sessions = array_keys($this->sessions);
        } else {
            array_map([ $this, 'builderMustExist' ], $session_names);
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
     * @param string $name
     * @return  Pomm      $this
     *@throws  FoundationException
     */
    private function builderMustExist(string $name): Pomm
    {
        if (!$this->hasBuilder($name)) {
            throw new FoundationException(
                sprintf(
                    "No such builder '%s'. Available builders are {%s}.",
                    $name,
                    join(
                        ', ',
                        array_map(
                            fn($val) => sprintf("'%s'", $val),
                            array_keys($this->getSessionBuilders())
                        )
                    )
                )
            );
        }

        return $this;
    }
}
