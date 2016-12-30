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

use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Exception\ConverterException;

/**
 * PgTimestampChronos
 *
 * Date and timestamp converter
 *
 * @package   Foundation
 * @copyright 2017 Grégoire HUBERT
 * @author    Miha Vrhovnik
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgTimestampChronos implements ConverterInterface
{
    const TS_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        $data = trim($data);

        return $data !== '' ? new Chronos($data) : null;
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
     * Ensure a ChronosInterface instance.
     *
     * @access protected
     * @param  mixed $data
     * @throws ConverterException
     * @return ChronosInterface
     */
    protected function checkData($data)
    {
        if (!$data instanceof ChronosInterface) {
            try {
                $data = new Chronos($data);
            } catch (\Exception $e) {
                throw new ConverterException(
                    sprintf(
                        "Cannot convert data from invalid datetime representation '%s'.",
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
