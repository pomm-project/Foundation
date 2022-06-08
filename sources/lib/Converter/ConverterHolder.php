<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use PommProject\Foundation\Exception\ConverterException;

/**
 * ConverterHolder
 *
 * Responsible of holding all converters associated with their corresponding
 * types.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ConverterHolder
{
    protected array $converters = [];
    protected array $types = [];

    /**
     * registerConverter
     *
     * Declare a converter and assign types to it.
     *
     * @access public
     * @param string $name
     * @param ConverterInterface $converter
     * @param array $types
     * @param bool|null $strict
     * @return ConverterHolder    $this
     * @throws ConverterException
     */
    public function registerConverter(string $name, ConverterInterface $converter, array $types, bool $strict = null): ConverterHolder
    {
        $this->addConverter($name, $converter, $strict);

        foreach ($types as $type) {
            $this->addTypeToConverter($name, $type);
        }

        return $this;
    }

    /**
     * addConverter
     *
     * Add a converter with a new name. If strict is set to true and the
     * converter for this type has already been registered, then it throws and
     * exception.
     *
     * @access protected
     * @param string $name
     * @param  ConverterInterface $converter
     * @param bool|null $strict (default true)
     * @return ConverterHolder    $this
     *@throws ConverterException if $name already exists and strict.
     */
    protected function addConverter(string $name, ConverterInterface $converter, bool $strict = null): ConverterHolder
    {
        $strict = $strict === null || $strict;

        if ($strict && $this->hasConverterName($name)) {
            throw new ConverterException(
                sprintf(
                    "A converter named '%s' already exists. (Known converters are {%s}).",
                    $name,
                    join(', ', $this->getConverterNames())
                )
            );
        }

        $this->converters[$name] = $converter;

        return $this;
    }

    /**
     * hasConverterName
     *
     * Tell if the converter exists or not.
     *
     * @access public
     * @param string $name
     * @return bool
     */
    public function hasConverterName(string $name): bool
    {
        return isset($this->converters[$name]);
    }

    /**
     * getConverter
     *
     * Return the converter associated with this name. If no converters found,
     * NULL is returned.
     *
     * @access public
     * @param string $name
     * @return ConverterInterface|null
     */
    public function getConverter(string $name): ?ConverterInterface
    {
        return $this->hasConverterName($name) ? $this->converters[$name] : null;
    }

    /**
     * getConverterNames
     *
     * Returns an array with the names of the registered converters.
     *
     * @access public
     * @return array
     */
    public function getConverterNames(): array
    {
        return array_keys($this->converters);
    }

    /**
     * addTypeToConverter
     *
     * Make the given converter to support a new PostgreSQL type. If the given
     * type is already defined, it is overrided with the new converter.
     *
     * @access public
     * @param string $name
     * @param string $type
     * @return ConverterHolder $this
     *@throws  ConverterException if $name does not exist.
     */
    public function addTypeToConverter(string $name, string $type): ConverterHolder
    {
        if (!$this->hasConverterName($name)) {
            throw new ConverterException(
                sprintf(
                    "No such converter name '%s'. Registered converters are {%s}.",
                    $name,
                    join(', ', $this->getConverterNames())
                )
            );
        }

        $this->types[$type] = $name;

        return $this;
    }

    /**
     * getConverterForType
     *
     * Returns the converter instance for the given type.
     *
     * @access public
     * @param string $type
     * @return ConverterInterface
     *@throws  ConverterException if there are no converters associated.
     */
    public function getConverterForType(string $type): ConverterInterface
    {
        if (!$this->hasType($type)) {
            throw new ConverterException(
                sprintf(
                    "No converters associated with type '%s'. Handled types are {%s}.",
                    $type,
                    join(', ', $this->getTypes())
                )
            );
        }

        return $this->converters[$this->types[$type]];
    }

    /**
     * hasType
     *
     * Does the type exist ?
     *
     * @access public
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /**
     * getTypes
     *
     * Return the list of handled types.
     *
     * @access public
     * @return array
     */
    public function getTypes(): array
    {
        return array_keys($this->types);
    }

    /**
     * getTypesWithConverterName
     *
     * Return the list of types with the related converter name.
     *
     * @access public
     * @return array
     */
    public function getTypesWithConverterName(): array
    {
        return $this->types;
    }
}
