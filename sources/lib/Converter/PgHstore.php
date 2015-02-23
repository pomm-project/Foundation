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
use PommProject\Foundation\Session\Session;

/**
 * PgHStore
 *
 * HStore converter
 *
 * @package Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ArrayTypeConverter
 */
class PgHstore extends ArrayTypeConverter
{
    /**
     * @see \Pomm\Converter\ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if ($data ===  null) {
            return null;
        }

        $hstore = null;
        $code = @eval(sprintf("\$hstore = [%s];", $data));

        if ($code !== null || !is_array($hstore)) {
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

        return sprintf("%s('%s')", $type, join(', ', $this->buildArray($this->checkArray($data), $session)));
    }

    /**
     * @see \Pomm\Converter\ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        return sprintf('"%s"', join(', ', $this->buildArray($this->checkArray($data), $session, '"')));
    }

    /**
     * buildArray
     *
     * Return an array of HStore elements.
     *
     * @access protected
     * @param  array $data
     * @return array
     */
    protected function buildArray(array $data, Session $session, $quote = "'")
    {
        $insert_values = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                $insert_values[] = sprintf('%s => NULL', str_replace($quote, $quote.$quote, $session->getConnection()->escapeIdentifier($key)));
            } else {
                $insert_values[] = sprintf(
                    '%s => %s',
                    str_replace($quote, $quote.$quote, $session->getConnection()->escapeIdentifier($key)),
                    str_replace($quote, $quote.$quote, $session->getConnection()->escapeIdentifier($value))
                );
            }
        }

        return $insert_values;
    }
}
