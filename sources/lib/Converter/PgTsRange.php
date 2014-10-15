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

use PommProject\Foundation\Converter\PgNumRange;

/**
 * PgTsRange
 *
 * Range for timestamps.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 * @see PgNumRange
 */
class PgTsRange extends PgNumRange
{
    public function getTypeClassName()
    {
        return '\PommProject\Foundation\Converter\Type\TsRange';
    }
}
