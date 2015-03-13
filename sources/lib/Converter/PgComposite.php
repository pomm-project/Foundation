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
 * PgComposite
 *
 *  Composite type converter.
 *
 * @package Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ArrayTypeConverter
 */
class PgComposite extends ArrayTypeConverter
{
    protected $structure;
    protected $converters;

    /**
     * __construct
     *
     * Takes the composite type structure as parameter.
     *
     * @access public
     * @param array $structure structure definition.
     */
    public function __construct(array $structure)
    {
        $this->structure = $structure;
    }

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        $values = str_getcsv(stripcslashes(trim($data, '()')));
        $out_values = array_flip(array_keys($this->structure));

        foreach ($out_values as $key => $value) {
            $out_values[$key] = $this
                ->getConverter($key, $session)
                ->fromPg($values[$value], $this->structure[$key], $session)
                ;
        }

        return $out_values;
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        $this->checkArray($data);

        return sprintf(
            "ROW(%s)::%s",
            join(',', $this->convertArray($data, $session, 'toPg')),
            $type
        );
    }

    public function toPgStandardFormat($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        $this->checkArray($data);

        return
            sprintf("(%s)",
                join(',', array_map(function($val) {
                    if ($val === null) {
                        return '';
                    } elseif (strlen($val) === 0) {
                        return '""';
                    } elseif (preg_match('/[,\s]/', $val)) {
                        return sprintf('"%s"', str_replace('"', '""', $val));
                    } else {
                        return $val;
                    };
                }, $this->convertArray($data, $session, 'toPgStandardFormat')
                ))
            );
    }

    /**
     * getConverter
     *
     * Return the converter for the given field.
     *
     * @access private
     * @param  string $key
     * @param  Session $session
     * @return ConverterInterface
     */
    private function getConverter($key, Session $session)
    {
        if (!isset($this->converters[$key])) {
            $this->converters[$key] = $session
                    ->getClientUsingPooler('converter', $this->structure[$key])
                    ->getConverter()
                    ;
        }

        return $this->converters[$key];
    }

    /**
     * convertArray
     *
     * Convert the given array of values.
     *
     * @access private
     * @param  array $data
     * @param  Session $session
     * @return array
     */
    private function convertArray(array $data, Session $session, $method)
    {
        $values = [];

        foreach ($this->structure as $name => $subtype) {
            $values[$name] = isset($data[$name])
                ? $this->getConverter($name, $session)
                    ->$method($data[$name], $subtype, $session)
                : $this->getConverter($name, $session)
                    ->$method(null, $subtype, $session)
                ;
        }

        return $values;
    }
}
