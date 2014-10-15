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
use PommProject\Foundation\Converter\TypeConverter;
use PommProject\Foundation\Session;

class PgNumRange extends TypeConverter
{
    /**
     * getTypeClassName
     *
     * @see TypeConverter
     */
    public function getTypeClassName()
    {
        return '\PommProject\Foundation\Converter\Type\NumRange';
    }

    public function toPg($data, $type, Session $session)
    {
        $data = parent::toPg($data, $type, $session);

        return sprintf("%s(%s)", $type, $data);
    }
}
