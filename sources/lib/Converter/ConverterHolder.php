<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ConverterHolder
{
    protected $converters = [];
    protected $types = [];

    /**
     * registerConverter
     *
     * Declare a converter and assign types to it.
     *
     * @access public
     * @param  string             $name
     * @param  ConverterInterface $converter
     * @param  array              $types
     * @param  bool               $strict
     * @return ConverterHolder    $this
     */
    public function registerConverter($name, ConverterInterface $converter, array $types, $strict = null)
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
     * Add a converter with a new name.
     *
     * @access protected
     * @param  string             $name
     * @param  ConverterInterface $converter
     * @param  bool               $strict (default true)
     * @throws  ConverterException if $name already exists and strict.
     * @return ConverterHolder    $this
     */
    protected function addConverter($name, ConverterInterface $converter, $strict = null)
    {
        $strict = $strict === null ? true : (bool) $strict;

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
     * @param  string $name
     * @return bool
     */
    public function hasConverterName($name)
    {
        return (bool) isset($this->converters[$name]);
    }

    /**
     * getConverter
     *
     * Return the converter associated with this name. If no converters found,
     * NULL is returned.
     *
     * @access public
     * @param  string             $name
     * @return ConverterInterface
     */
    public function getConverter($name)
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
    public function getConverterNames()
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
     * @param  string          $name
     * @param  string          $type
     * @throws  ConverterException if $name does not exist.
     * @return ConverterHolder $this
     */
    public function addTypeToConverter($name, $type)
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
     * @param  string             $type
     * @throws  ConverterException if there are no converters associated.
     * @return ConverterInterface
     */
    public function getConverterForType($type)
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
     * @param  string $type
     * @return bool
     */
    public function hasType($type)
    {
        return (bool) isset($this->types[$type]);
    }

    /**
     * getTypes
     *
     * Return the list of handled types.
     *
     * @access public
     * @return array
     */
    public function getTypes()
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
    public function getTypesWithConverterName()
    {
        return $this->types;
    }
}
