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
 * PgLtree
 *
 * Ltree converter.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgLtree implements ConverterInterface
{
    /**
     * fromPg
     *
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        $data = trim($data);

        if ($data === '') {
            return null;
        }

        return preg_split('/\./', $data);
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
            ? sprintf("ltree '%s'", join('.', $this->checkData($data)))
            : sprintf("NULL::%s", $type)
            ;
    }


    /**
     * toCsv
     *
     * @see ConverterInterface
     */
    public function toCsv($data, $type, Session $session)
    {
        return
            $data !== null
            ? sprintf("%s", join('.', $this->checkData($data)))
            : null
            ;
    }

    /**
     * checkData
     *
     * Check if data is a suitable LTree PHP representation.
     *
     * @access protected
     * @param  mixed $data
     * @throw  ConverterException
     * @return array
     */
    protected function checkData($data)
    {
        if (!is_array($data)) {

            throw new ConverterException(
                sprintf(
                    "Ltree output converter expects data to be an array, '%s' given.",
                    gettype($data)
                )
            );
        }

        return $data;
    }
}
