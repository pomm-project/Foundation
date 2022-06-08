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

use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Session\Session;

/**
 * PgBoolean
 *
 * Converter for boolean type.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgBoolean implements ConverterInterface
{
    /**
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function fromPg(?string $data, string $type, Session $session): ?bool
    {
        if (null === $data) {
            return null;
        }
        $data = trim($data);

        if (!preg_match('/^(t|f)$/', $data)) {
            if ($data === '') {
                return null;
            }

            throw new ConverterException(sprintf("Unknown %s data '%s'.", $type, $data));
        }

        return $data === 't';
    }

    /**
     * @see ConverterInterface
     */
    public function toPg(mixed $data, string $type, Session $session): string
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        return sprintf("%s '%s'", $type, $data === true ? "true" : "false");
    }

    /**
     * @see ConverterInterface
     */
    public function toPgStandardFormat(mixed $data, string $type, Session $session): ?string
    {
        if ($data !== null) {
            return $data === true ? 't' : 'f';
        }

        return null;
    }
}
