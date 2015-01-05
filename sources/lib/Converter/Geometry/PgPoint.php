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
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Session\Session;

class PgPoint extends TypeConverter
{
    public function getTypeClassName()
    {
        return  '\PommProject\Foundation\Converter\Type\Point';
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if (($data = $this->checkData($data, $type, $session)) === null) {
            return sprintf("NULL::%s", $type);
        }

        return sprintf(
            "point(%s,%s)",
            $data->x,
            $data->y
        );
    }
}
