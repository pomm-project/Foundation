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

use PommProject\Foundation\Converter\ArrayTypeConverter;
use PommProject\Foundation\Session\Session;

/**
 * PgArray
 *
 * Converter for arrays.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ArrayTypeConverter
 */
class PgArray extends ArrayTypeConverter
{
    /**
     * getSubType
     *
     * Extact subtype from a formatted string (ie int4[] or _text).
     *
     * @static
     * @access public
     * @param  string $type
     * @return string
     */
    public static function getSubType($type)
    {
        if (preg_match('/^(.+)\[\]$/', $type, $matches) || preg_match('/^_(.+)$/', $type, $matches)) {
            return $matches[1];
        }

        return $type;
    }

    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data === null || $data === 'NULL') {
            return null;
        }

        $type = $this->getSubType($type);

        if ($data !== "{}") {
            $converter = $this->getSubtypeConverter($type, $session);

            return array_map(function ($val) use ($converter, $type, $session) {
                if ($val !== "NULL") {
                    return preg_match('/\\\\/', $val)
                        ? $converter->fromPg(stripslashes($val), $type, $session)
                        : $converter->fromPg($val, $type, $session);
                } else {
                    return null;
                }
                }, str_getcsv(trim($data, "{}")));
        } else {
            return [];
        }
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        $type = $this->getSubType($type);

        if ($data === null) {
                return sprintf("NULL::%s[]", $type);
        }

        $converter = $this->getSubtypeConverter($type, $session);
        $data = $this->checkArray($data);

        return sprintf('ARRAY[%s]::%s[]', join(',', array_map(function ($val) use ($converter, $type, $session) {
                    return $converter->toPg($val, $type, $session);
                }, $data)), $type);
    }

    /**
     * @see ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        $type = $this->getSubType($type);
        $converter = $this->getSubtypeConverter($type, $session);
        $data = $this->checkArray($data);

        return
            sprintf('{%s}', join(',',
                array_map(function ($val) use ($converter, $type, $session) {
                    if ($val === null) {
                        return 'NULL';
                    }

                    $val = $converter->toPgStandardFormat($val, $type, $session);

                    if (strlen($val) !== 0) {
                        if (preg_match('/[,\\"\s]/', $val)) {
                            $val = sprintf('"%s"', addcslashes($val, '"\\'));
                        }
                    } else {
                        $val = '""';
                    }

                    return $val;
                }, $data)
                ));
    }
}
