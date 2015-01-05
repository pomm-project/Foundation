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
            "circle(%s,%s)",
            $session
                ->getClientUsingPooler('converter', 'point')
                ->toPg($data->center, 'point', $session),
            $data->radius
        );
    }
}
