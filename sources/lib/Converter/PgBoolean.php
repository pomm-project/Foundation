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
 * PgBoolean
 *
 * Converter for boolean type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgBoolean implements ConverterInterface
{
    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        $data = trim($data);

        if (!preg_match('/^(t|f)$/', $data)) {
            if ($data === '') {
                return null;
            }

            throw new ConverterException(sprintf("Unknown %s data '%s'.", $type, $data));
        }

        return (bool) ($data === 't');
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) return sprintf("NULL::%s", $type);
        return sprintf("%s '%s'", $type, $data === true ? "true" : "false");
    }

    /**
     * @see ConverterInterface
     */
    public function toCsv($data, $type, Session $session)
    {
        return
            $data !== null
            ? $data === true ? 't' : 'f'
            : null
            ;
    }
}
