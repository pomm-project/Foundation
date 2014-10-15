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

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Session;

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
        if ($data === null) {
            return null;
        }

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

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        $class = $this->getTypeClassName();

        if (!$data instanceOf $class) {
            $data = $this->fromPg($data, $type, $session);
        }

        return $data->__toString();
    }
}
