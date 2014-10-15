<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter\Geometry;

use PommProject\Foundation\Converter\TypeConverter;

/**
 * PgCircle
 *
 * Converter for Postgresql Circle type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgCircle extends TypeConverter
{
    /**
     * getTypeClassName
     *
     * Circle class name
     *
     * @see TypeConverter
     */
    protected function getTypeClassName()
    {
        return '\PommProject\Foundation\Converter\Type\Circle';
    }
}
