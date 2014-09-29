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
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Session;

/**
 * PgArray
 *
 * Converter for arrays.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgArray implements ConverterInterface
{
    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data === '') return null;

        if ($data !== "{NULL}" && $data !== "{}") {
            $converter = $session
                ->getPoolerForType('converter')
                ->getConverterForType($type)
                ;

            return array_map(function ($val) use ($converter, $type) {
                    return $val !== "NULL" ? $converter->fromPg($val, $type) : null;
                }, str_getcsv(trim($data, "{}")));
        } else {
            return array();
        }
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if (!is_array($data)) {
            if (is_null($data)) {
                return 'NULL';
            }

            throw new ConverterException(sprintf("Array converter toPg() data must be an array ('%s' given).", gettype($data)));
        }

        $converter = $session
            ->getPoolerForType('converter')
            ->getConverterForType($type)
            ;

        return sprintf('ARRAY[%s]::%s[]', join(',', array_map(function ($val) use ($converter, $type) {
                    return $converter->toPg($val, $type);
                }, $data)), $type);
    }
}
