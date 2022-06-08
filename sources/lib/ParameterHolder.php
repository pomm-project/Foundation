<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ParameterHolder implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * __construct()
     *
     * @access public
     * @param  array $parameters (optional)
     */
    public function __construct(protected array $parameters = [])
    {
    }

    /**
     * setParameter
     *
     * Set a parameter.
     *
     * @access public
     * @param string $name
     * @param bool|string|array|null $value
     * @return ParameterHolder $this
     */
    public function setParameter(string $name, bool|string|array|null $value): ParameterHolder
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * hasParameter
     *
     * check if the given parameter exists.
     *
     * @access public
     * @param  string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * getParameter
     *
     * Returns the parameter "name" or "default" if not set.
     *
     * @access public
     * @param  string       $name
     * @param  bool|string|array|null $default Optional default value if name not set.
     * @return bool|string|array|null Parameter's value or default.
     */
    public function getParameter(string $name, bool|string|array|null $default = null): bool|string|array|null
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    /**
     * mustHave()
     *
     * Throw an exception if a param is not set
     *
     * @access public
     * @throws  FoundationException
     * @param  string          $name the parameter's name
     * @return ParameterHolder $this
     */
    public function mustHave(string $name): ParameterHolder
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
     * @access public
     * @param  string          $name  the parameter's name
     * @param  mixed           $value the default value
     * @return ParameterHolder $this
     */
    public function setDefaultValue(string $name, mixed $value): ParameterHolder
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
     * @access public
     * @throws  FoundationException
     * @param  string          $name   the parameter's name
     * @param  array           $values
     * @return ParameterHolder $this
     */
    public function mustBeOneOf(string $name, array $values): ParameterHolder
    {
        if (!in_array($this[$name], $values)) {
            throw new FoundationException(sprintf('The parameters "%s" must be one of [%s].', $name, implode(', ', $values)));
        }

        return $this;
    }

    /**
     * unsetParameter()
     *
     * @access public
     * @param  string          $name
     * @return ParameterHolder $this
     */
    public function unsetParameter(string $name): ParameterHolder
    {
        unset($this->parameters[$name]);

        return $this;
    }

    /**
     * offsetExists()
     *
     * @see ArrayAccess
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasParameter($offset);
    }

    /**
     * offsetGet()
     *
     * @see ArrayAccess
     */
    public function offsetGet(mixed $offset): array|string|null
    {
        return $this->getParameter($offset);
    }

    /**
     * offsetSet()
     *
     * @see ArrayAccess
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setParameter($offset, $value);
    }

    /**
     * offsetUnset()
     *
     * @see ArrayAccess
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unsetParameter($offset);
    }

    /**
     *
     * @see \Countable
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * getIterator()
     *
     * @see \IteratorAggregate
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
    }
}
