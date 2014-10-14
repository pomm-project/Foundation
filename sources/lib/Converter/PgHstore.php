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

use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Session;

/**
 * PgHStore
 *
 * HStore converter
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgHstore implements ConverterInterface
{
    /**
     * @see \Pomm\Converter\ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data ===  null) {
            return null;
        }

        @eval(sprintf("\$hstore = [%s];", $data));

        if (!(isset($hstore) && is_array($hstore))) {
            throw new ConverterException(sprintf("Could not parse hstore string '%s' to array.", $data));
        }

        return $hstore;
    }

    /**
     * @see \Pomm\Converter\ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        if (!is_array($data)) {
            throw new ConverterException(sprintf("HStore::toPg takes an associative array as parameter ('%s' given).", gettype($data)));
        }

        $insert_values = [];

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $insert_values[] = sprintf('"%s" => NULL', $key);
            } else {
                $insert_values[] = sprintf(
                    '%s => %s',
                    $session->getConnection()->escapeIdentifier($key),
                    $session->getConnection()->escapeIdentifier(str_replace("'", "''", $value))
                );
            }
        }

        return sprintf("%s('%s')", $type, join(', ', $insert_values));
    }
}
