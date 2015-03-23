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
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Session\Session;

/**
 * ArrayTypeConverter
 *
 * Array sub class for converters using a PHP array representation.
 *
 * @package Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 * @abstract
 */
abstract class ArrayTypeConverter implements ConverterInterface
{
    protected $converters = [];

    /**
     * checkArray
     *
     * Check if the data is an array.
     *
     * @access protected
     * @param  mixed    $data
     * @throws ConverterException
     * @return array    $data
     */
    protected function checkArray($data)
    {
        if (!is_array($data)) {
            throw new ConverterException(
                sprintf(
                    "Array converter data must be an array ('%s' given).",
                    gettype($data)
                )
            );
        }

        return $data;
    }

    /**
     * convertArray
     *
     * Convert the given array of values.
     *
     * @access private
     * @param  array $data
     * @param  Session $session
     * @return array
     */
    private function convertArray(array $data, Session $session, $method)
    {
        $values = [];

        foreach ($this->structure as $name => $subtype) {
            $values[$name] = isset($data[$name])
                ? $this->getConverter($name, $session)
                    ->$method($data[$name], $subtype, $session)
                : $this->getConverter($name, $session)
                    ->$method(null, $subtype, $session)
                ;
        }

        return $values;
    }

    /**
     * getSubtypeConverter
     *
     * Since the arrays in Postgresql have the same sub type, it is useful to
     * cache it here to ovoid summoning the ClientHolder all the time.
     *
     * @access protected
     * @param  string   $type
     * @param  Session  $session
     * @return ConverterInterface
     */
    protected function getSubtypeConverter($type, Session $session)
    {
        if (!isset($this->subtype_converter[$type])) {
            $this->converters[$type] = $session
                ->getClientUsingPooler('converter', $type)
                ->getConverter()
                ;
        }

        return $this->converters[$type];
    }
}
