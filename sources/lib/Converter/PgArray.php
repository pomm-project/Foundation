<?php
namespace Pomm\Converter;

/**
 * Pomm\Converter\pgArray - Array converter
 *
 * @package Pomm
 * @version $id$
 * @copyright 2011 - 2013 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PgArray implements ConverterInterface
{
    protected $converter_holder;

    /**
     * __construct
     *
     * @param \Pomm\Connection\Database $database
     */
    public function __construct(\Pomm\Converter\ConverterHolder $converter_holder)
    {
        $this->converter_holder = $converter_holder;
    }

    /**
     * @see Pomm\Converter\ConverterInterface
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
     * @see Pomm\Converter\ConverterInterface
     */
    public function toPg($data, $type)
    {
        if (!is_array($data)) {
            if (is_null($data)) {
                return 'NULL';
            }

            throw new Exception(sprintf("Array converter toPg() data must be an array ('%s' given).", gettype($data)));
        }

        $converter = $this->converter_holder
            ->getConverterForType($type);

        return sprintf('ARRAY[%s]::%s[]', join(',', array_map(function ($val) use ($converter, $type) {
                    return !is_null($val) ? $converter->toPg($val, $type) : 'NULL';
                }, $data)), $type);
    }
}
