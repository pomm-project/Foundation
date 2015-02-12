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

    protected $subtype_converter = [];

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
        if (preg_match('/^(.+)\[\]$/', $type, $matchs) || preg_match('/^_(.+)$/', $type, $matchs)) {
            return $matchs[1];
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

            return array_map(function ($val) use ($converter, $type) {
                    return $val !== "NULL" ? $converter->fromPg($val, $type) : null;
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

        return sprintf('ARRAY[%s]::%s[]', join(',', array_map(function ($val) use ($converter, $type) {
                    return $converter->toPg($val, $type);
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
                array_map(function ($val) use ($converter, $type) {
                    $val = $converter->toPgStandardFormat($val, $type);

                    return $val !== null ? $val : 'NULL';
                }, $data)
                ));
    }

    /**
     * checkArray
     *
     * Check if the data is an array.
     *
     * @access protected
     * @param  mixed    $data
     * @return array    $data
     */
    protected function checkArray($data)
    {
        if (!is_array($data)) {
            throw new ConverterException(
                sprintf(
                    "Array converter data must be an array ('%s' given).",
                    gettype($data)
                )
            );
        }

        return $data;
    }

    /**
     * getSubtypeConverter
     *
     * Since the arrays in Postgresql have the same sub type, it is useful to
     * cache it here to ovoid summoning the ClientHolder all the time.
     *
     * @access protected
     * @param  string   $type
     * @param  Session  $session
     * @return ConverterInterface
     */
    protected function getSubtypeConverter($type, Session $session)
    {
        if (!isset($this->subtype_converter[$type])) {
            $this->subtype_converter[$type] = $session->getClientUsingPooler('converter', $type);
        }

        return $this->subtype_converter[$type];
    }
}
