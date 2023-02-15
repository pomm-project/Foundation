<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Exception\FoundationException;

/**
 * ParameterHolder
 *
 * @package   Foundation
 * @copyright 2014 - 2017 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ParameterHolder implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $parameters;

    /**
     * __construct()
     *
     * @param  array $parameters (optional)
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * setParameter
     *
     * Set a parameter.
     *
     * @param  string          $name
     * @param  string|array    $value
     * @return ParameterHolder $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * hasParameter
     *
     * check if the given parameter exists.
     *
     * @param  string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * getParameter
     *
     * Returns the parameter "name" or "default" if not set.
     *
     * @param  string       $name
     * @param  string       $default Optional default value if name not set.
     * @return string|array Parameter's value or default.
     */
    public function getParameter($name, $default = null)
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    /**
     * mustHave()
     *
     * Throw an exception if a param is not set
     *
     * @throws  FoundationException
     * @param  string          $name the parameter's name
     * @return ParameterHolder $this
     */
    public function mustHave($name)
    {
        if (!$this->hasParameter($name)) {
            throw new FoundationException(sprintf('The parameter "%s" is mandatory.', $name));
        }

        return $this;
    }

    /**
     * setDefaultValue()
     *
     * Sets a default value if the param $name is not set
     *
     * @param  string          $name  the parameter's name
     * @param  mixed           $value the default value
     * @return ParameterHolder $this
     */
    public function setDefaultValue($name, $value)
    {
        if (!$this->hasParameter($name)) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    /**
     * mustBeOneOf()
     *
     * Check if the given parameter is one of the values passed as argument. If
     * not, an exception is thrown.
     *
     * @throws  FoundationException
     * @param  string          $name   the parameter's name
     * @param  array           $values
     * @return ParameterHolder $this
     */
    public function mustBeOneOf($name, array $values)
    {
        if (!in_array($this[$name], $values)) {
            throw new FoundationException(
                sprintf('The parameters "%s" must be one of [%s].', $name, implode(', ', $values))
            );
        }

        return $this;
    }

    /**
     * unsetParameter()
     *
     * @param  string          $name
     * @return ParameterHolder $this
     */
    public function unsetParameter($name)
    {
        unset($this->parameters[$name]);

        return $this;
    }

    /**
     * offsetExists()
     *
     * @see ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($name): bool
    {
        return $this->hasParameter($name);
    }

    /**
     * offsetGet()
     *
     * @see ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($name): mixed
    {
        return $this->getParameter($name);
    }

    /**
     * offsetSet()
     *
     * @see ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($name, $value): void
    {
        $this->setParameter($name, $value);
    }

    /**
     * offsetUnset()
     *
     * @see ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($name): void
    {
        $this->unsetParameter($name);
    }

    /**
     *
     * @see \Countable
     */
    #[\ReturnTypeWillChange]
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * getIterator()
     *
     * @see \IteratorAggregate
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->parameters);
    }
}
