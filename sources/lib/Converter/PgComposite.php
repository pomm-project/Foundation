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
     * The structure is $name => $type.
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

        return $this->convertArray(array_combine(array_keys($this->structure), $values), $session, 'fromPg');
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

    /**
     * toPgStandardFormat
     *
     * @see ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        $this->checkArray($data);

        return
            sprintf("(%s)",
                join(',', array_map(function ($val) {
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
     * convertArray
     *
     * Convert the given array of values.
     *
     * @access private
     * @param  array $data
     * @param  Session $session
     * @param  string $method
     * @return array
     */
    private function convertArray(array $data, Session $session, $method)
    {
        $values = [];

        foreach ($this->structure as $name => $subtype) {
            $values[$name] = isset($data[$name])
                ? $this->getSubtypeConverter($subtype, $session)
                    ->$method($data[$name], $subtype, $session)
                : $this->getSubtypeConverter($subtype, $session)
                    ->$method(null, $subtype, $session)
                ;
        }

        return $values;
    }
}
