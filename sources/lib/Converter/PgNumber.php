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
 * PgNumber
 *
 * Converter for numbers.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgNumber implements ConverterInterface
{
    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type)
    {
        $data = trim($data);

        if ($data === '') {
            return null;
        }

        return $data + 0;
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type)
    {
        if ($data != null) {
            return sprintf("%s '%s'", $type, $data + 0);
        } else {
            return sprintf("NULL::%s", $type);
        }
    }
}
