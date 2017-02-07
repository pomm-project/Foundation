<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use Cake\Chronos\Date;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Exception\ConverterException;

/**
 * PgDateChronos
 *
 * Date converter
 *
 * @package   Foundation
 * @copyright 2017 Grégoire HUBERT
 * @author    Miha Vrhovnik
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgDateChronos implements ConverterInterface
{
    const TS_FORMAT = 'Y-m-d';

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        $data = trim($data);

        return $data !== '' ? new Date($data) : null;
    }

    /**
     * toPg
     *
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf("%s '%s'", $type, $this->checkData($data)->format(static::TS_FORMAT))
            : sprintf("NULL::%s", $type)
            ;
    }

    /**
     * toPgStandardFormat
     *
     * @see ConverterInterface
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        return
            $data !== null
            ? $this->checkData($data)->format(static::TS_FORMAT)
            : null
            ;
    }

    /**
     * checkData
     *
     * Ensure a Date instance.
     *
     * @access protected
     * @param  mixed $data
     * @throws ConverterException
     * @return Date
     */
    protected function checkData($data)
    {
        if (!$data instanceof Date) {
            try {
                $data = new Date($data);
            } catch (\Exception $e) {
                throw new ConverterException(
                    sprintf(
                        "Cannot convert data from invalid date representation '%s'.",
                        $data
                    ),
                    null,
                    $e
                );
            }
        }

        return $data;
    }
}
