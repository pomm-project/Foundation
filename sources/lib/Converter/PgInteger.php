<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use PommProject\Foundation\Session\Session;

/**
 * PgInteger
 *
 * Converter for numbers.
 *
 * @package   Foundation
 * @copyright 2014 - 2017 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgInteger implements ConverterInterface
{
    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if (null === $data) {
            return null;
        }
        $data = trim($data);

        if ($data === '') {
            return null;
        }

        return (int)$data;
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf("%s '%u'", $type, $data)
            : sprintf("NULL::%s", $type)
            ;
    }

    /**
     * toPgStandardFormat
     *
     * @see ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf('%u', $data)
            : null
            ;
    }
}
