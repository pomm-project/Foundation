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

/**
 * PgString
 *
 * Converter for strings types.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgString implements ConverterInterface
{
    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type = null)
    {
        $data = pg_escape_string($data);
        $type = is_null($type) ? '' : sprintf("%s ", $type);
        $data = sprintf("%s'%s'",  $type, $data);

        return $data;
    }

    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type = null)
    {
        return $data;
    }
}
