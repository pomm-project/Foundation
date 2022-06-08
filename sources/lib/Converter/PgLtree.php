<?php
/*
 * This file is part of the Pomm package.
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
 * PgLtree
 *
 * Ltree converter.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgLtree extends ArrayTypeConverter
{
    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg(?string $data, string $type, Session $session): array|bool|null
    {
        if (null === $data) {
            return null;
        }
        $data = trim($data);

        if ($data === '') {
            return null;
        }

        return preg_split('/\./', $data);
    }

    /**
     * toPg
     *
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function toPg(mixed $data, string $type, Session $session): string
    {
        return
            $data !== null
            ? sprintf("ltree '%s'", join('.', $this->checkArray($data)))
            : sprintf("NULL::%s", $type)
            ;
    }


    /**
     * toPgStandardFormat
     *
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function toPgStandardFormat(mixed $data, string $type, Session $session): ?string
    {
        return
            $data !== null
            ? sprintf("%s", join('.', $this->checkArray($data)))
            : null
            ;
    }
}
