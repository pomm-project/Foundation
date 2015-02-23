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
}
