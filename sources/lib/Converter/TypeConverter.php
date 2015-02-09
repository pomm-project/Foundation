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
use PommProject\Foundation\Converter\Type\BaseRange;
use PommProject\Foundation\Session\Session;

/**
 * TypeConverter
 *
 * Abstract class for converter that use object types like point, circle,
 * numrange etc.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @abstract
 */
abstract class TypeConverter implements ConverterInterface
{
    protected $class_name;

    /**
     * getTypeClassName
     *
     * Return the type class name
     *
     * @access protected
     * @return string
     */
    abstract protected function getTypeClassName();

    /**
     * __construct
     *
     * Set the type class name.
     *
     * @access public
     * @param  string $class_name
     * @return void
     */
    public function __construct($class_name = null)
    {
        $this->class_name =
            $class_name === null
            ? $this->getTypeClassName()
            : $class_name
            ;
    }

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        return
            $data !== null
            ? $this->createObjectFrom($data)
            : null
            ;
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf("%s('%s')", $type, $this->checkData($data)->__toString())
            : sprintf("NULL::%s", $type)
            ;
    }

    /**
     * toPgStandardFormat
     *
     * @see ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf("%s", str_replace('"', '""', $this->checkData($data)->__toString()))
            : null
            ;
    }

    /**
     * checkData
     *
     * Check if data is suitable for Pg conversion. If not an attempt is made to build the object from the given definition.
     *
     * @access public
     * @param  mixed    $data
     * @return object
     */
    public function checkData($data)
    {
        $class_name = $this->getTypeClassName();

        if (!$data instanceof $class_name) {
            $data = $this->createObjectFrom($data);
        }

        return $data;
    }

    /**
     * createObjectFrom
     *
     * Create a range object from a given definition. If the object creation
     * fails, an exception is thrown.
     *
     * @access protected
     * @param  mixed $data
     * @return BaseRange
     */
    protected function createObjectFrom($data)
    {
        $class_name = $this->class_name;

        try {
            return new $class_name($data);
        } catch (\InvalidArgumentException $e) {
            throw new ConverterException(
                sprintf("Unable to create a '%s' instance.", $class_name),
                null,
                $e
            );
        }
    }
}
