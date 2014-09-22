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

use PommProject\Foundation\Converter\ConverterHolder;
use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Converter\ConverterInterface;

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
    protected $converter_holder;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  ConverterHolder $converter_holder
     * @return void
     */
    public function __construct(ConverterHolder $converter_holder)
    {
        $this->converter_holder = $converter_holder;
    }

    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type)
    {
        if ($data === '') return null;

        if ($data !== "{NULL}" && $data !== "{}") {
            $converter = $this->converter_holder
                ->getConverterForType($type);

            return array_map(function ($val) use ($converter, $type) {
                    return $val !== "NULL" ? $converter->fromPg(str_replace(array('\\\\', '\\"'), array('\\', '"'), $val), $type) : null;
                }, str_getcsv(trim($data, "{}")));
        } else {
            return array();
        }
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type)
    {
        if (!is_array($data)) {
            if (is_null($data)) {
                return 'NULL';
            }

            throw new ConverterException(sprintf("Array converter toPg() data must be an array ('%s' given).", gettype($data)));
        }

        $converter = $this->converter_holder
            ->getConverterForType($type);

        return sprintf('ARRAY[%s]::%s[]', join(',', array_map(function ($val) use ($converter, $type) {
                    return !is_null($val) ? $converter->toPg($val, $type) : 'NULL';
                }, $data)), $type);
    }
}
