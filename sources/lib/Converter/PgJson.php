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
use PommProject\Foundation\Session\Session;

/**
 * PgJson
 *
 * Json converter.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgJson implements ConverterInterface
{
    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if (trim($data) === '') {
            return null;
        }

        $return = json_decode($data, true);

        if ($return === false) {
            throw new ConverterException(
                sprintf(
                    "Could not convert given Json '%s' to PHP array, '%s' error reported.",
                    $data,
                    json_last_error()
                )
            );
        }

        return $return;
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        } elseif (!is_array($data)) {
            throw new ConverterException(
                sprintf(
                    "Json data is not an array: '%s' given",
                    gettype($data)
                )
            );
        }

        return sprintf("json '%s'", json_encode($data));
    }
}
