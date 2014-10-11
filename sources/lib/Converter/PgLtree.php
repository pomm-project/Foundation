<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Session;

/**
 * PgLtree
 *
 * Ltree converter.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgLtree implements ConverterInterface
{
    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data === 'NULL' || $data === '') {
            return null;
        }

        return preg_split('/\./', $data);
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {

            return 'NULL';
        } else if (!is_array($data)) {

            throw new ConverterException(
                sprintf(
                    "Ltree data must be an array, '%s' given.",
                    gettype($data)
                )
            );
        }

        return sprintf("ltree '%s'", join('.', $data));
    }
}
