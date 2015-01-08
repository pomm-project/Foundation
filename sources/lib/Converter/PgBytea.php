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

use PommProject\Foundation\Session\Session;

/**
 * PgBytea
 *
 * Bytea converter
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PgBytea implements ConverterInterface
{
    /**
     * @see Pomm\Converter\ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) return sprintf("NULL::%s", $type);
        return sprintf(
            "%s '%s'",
            $type,
            preg_replace(["/\\\\/", "/''/"], ["\\", "'"], $session->getConnection()->escapeBytea($data))
        );
    }

    /**
     * @see Pomm\Converter\ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        return $session->getConnection()->unescapeBytea($data);
    }

    /**
     * @see Pomm\Converter\ConverterInterface
     */
    public function toCsv($data, $type, Session $session)
    {
        if ($data === null) return null;

        return sprintf(
            '"%s"',
            preg_replace(["/\\\\/", '/"/'], ["\\", '""'], $session->getConnection()->escapeBytea($data))
        );
    }
}
