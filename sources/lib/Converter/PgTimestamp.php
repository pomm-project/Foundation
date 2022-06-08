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

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Exception\ConverterException;

/**
 * PgTimestamp
 *
 * Date and timestamp converter
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgTimestamp implements ConverterInterface
{
    final const TS_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg(?string $data, string $type, Session $session): ?\DateTime
    {
        if (null === $data) {
            return null;
        }
        $data = trim($data);

        return $data !== '' ? new \DateTime($data) : null;
    }

    /**
     * toPg
     *
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function toPg(mixed $data, string $type, Session $session): string
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
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function toPgStandardFormat(mixed $data, string $type, Session $session): ?string
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
     * Ensure a DateTime instance.
     *
     * @access protected
     * @param  mixed $data
     * @throws ConverterException
     * @return \DateTime
     */
    protected function checkData(mixed $data): \DateTime
    {
        if (!$data instanceof \DateTime) {
            try {
                $data = new \DateTime($data);
            } catch (\Exception $e) {
                throw new ConverterException(
                    sprintf(
                        "Cannot convert data from invalid datetime representation '%s'.",
                        $data
                    ),
                    0,
                    $e
                );
            }
        }

        return $data;
    }
}
