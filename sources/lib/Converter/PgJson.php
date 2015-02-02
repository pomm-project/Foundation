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
 * PgJson
 *
 * Json converter.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgJson implements ConverterInterface
{
    protected $is_array;

    /**
     * __construct
     *
     * Configure the JSON converter to decode JSON as StdObject instances or
     * arrays (default).
     *
     * @access public
     * @param boolean $is_array
     */
    public function __construct($is_array = null)
    {
        $this->is_array = $is_array === null ? true : $is_array;
    }

    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if (trim($data) === '') {
            return null;
        }

        $return = json_decode($data, $this->is_array);

        if ($return === false) {
            throw new ConverterException(
                sprintf(
                    "Could not convert Json to PHP array, '%s' error reported.",
                    json_last_error()
                )
            );
        }

        return $return;
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

        $json = json_encode($data);

        if ($json === false) {
            throw new ConverterException(
                sprintf(
                    "Error '%s' while encoding data to JSON.",
                    json_last_error()
                )
            );
        }

        return sprintf("%s '%s'", $type, $json);
    }
}
