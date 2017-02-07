<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

/**
 * PgDateRangeChronos
 *
 * Range for date.
 *
 * @package   Foundation
 * @copyright 2017 Grégoire HUBERT
 * @author    Miha Vrhovnik
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       TypeConverter
 */
class PgDateRangeChronos extends TypeConverter
{
    /**
     * getTypeClassName
     *
     * @see TypeConverter
     */
    public function getTypeClassName()
    {
        return '\PommProject\Foundation\Converter\Type\DateRangeChronos';
    }
}
