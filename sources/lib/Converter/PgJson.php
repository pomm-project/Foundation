<?php
/*
 * This file is part of Pomm's Foundation package.
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
 * PgJson
 *
 * Json converter.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */
class PgJson implements ConverterInterface
{
    protected bool $is_array;

    /**
     * __construct
     *
     * Configure the JSON converter to decode JSON as StdObject instances or
     * arrays (default).
     *
     * @access public
     * @param boolean|null $is_array
     */
    public function __construct(bool $is_array = null)
    {
        $this->is_array = $is_array ?? true;
    }

    /**
     * fromPg
     *
     * @throws ConverterException
     * @see ConverterInterface
     */
    public function fromPg(?string $data, string $type, Session $session): mixed
    {
        if (null === $data) {
            return null;
        }
        if (trim($data) === '') {
            return null;
        }

        $return = json_decode($data, $this->is_array);

        if ($return === false) {
            throw new ConverterException(
                sprintf(
                    "Could not convert Json to PHP %s, driver said '%s'.\n%s",
                    $this->is_array ? 'array' : 'object',
                    json_last_error(),
                    $data
                )
            );
        }

        return $return;
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
            ? sprintf("json '%s'", $this->jsonEncode($data))
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
            ? $this->jsonEncode($data)
            : null
            ;
    }

    /**
     * jsonEncode
     *
     * Encode data to Json. Throw an exception if an error occurs.
     *
     * @access protected
     * @param  mixed $data
     * @throws  ConverterException
     * @return string
     */
    protected function jsonEncode(mixed $data): string
    {
        $return = json_encode($data);

        if ($return === false) {
            throw new ConverterException(
                sprintf(
                    "Could not convert %s data to JSON. Driver returned '%s'.",
                    gettype($data),
                    json_last_error()
                )
            );
        }

        return $return;
    }
}
