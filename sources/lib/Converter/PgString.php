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

use PommProject\Foundation\Exception\ConnectionException;
use PommProject\Foundation\Session\Session;

/**
 * PgString
 *
 * Converter for strings types.
 *
 * @package     Foundation
 * @copyright   2014 - 2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see         ConverterInterface
 */
class PgString implements ConverterInterface
{
    /**
     * toPg
     *
     * @throws ConnectionException
     * @see ConverterInterface
     */
    public function toPg(mixed $data, string $type, Session $session): string
    {
        return $data !== null
            ? sprintf("%s %s",  $type, $session->getConnection()->escapeLiteral($data))
            : sprintf("NULL::%s", $type)
            ;
    }

    /**
     * toPgStandardFormat
     *
     * @see ConverterInterface
     */
    public function toPgStandardFormat(mixed $data, string $type, Session $session): ?string
    {
        return $data;
    }

    /**
     * @see ConverterInterface
     */
    public function fromPg(?string $data, string $type, Session $session): ?string
    {
        return $data !== null ? $data : null;
    }
}
