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

class PgPoint extends TypeConverter
{
    public function getTypeClassName()
    {
        return  '\PommProject\Foundation\Converter\Type\Point';
    }
}
