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
 * PgInterval
 *
 * Convert an ISO8601 interval from/to PHP.
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PgInterval implements ConverterInterface
{
    /**
     * @see ConverterInterface
     */
    public function fromPg($data, $type, Session $session)
    {
        if (trim($data) === '') {
            return null;
        }

        try {
            return new \DateInterval(preg_replace('/\.[0-9]+S/', 'S', $data));
        } catch (\Exception $e) {
            throw new ConverterException(sprintf("Data '%s' is not an ISO8601 interval representation.", $data));
        }
    }

    /**
     * @see ConverterInterface
     */
    public function toPg($data, $type, Session $session)
    {
        return $data !== null
            ? sprintf("%s '%s'", $type, $this->checkData($data)->format('%Y years %M months %D days %H:%i:%S'))
            : sprintf("NULL::%s", $type)
            ;
    }


    /**
     * @see ConverterInterface
     */
    public function toCsv($data, $type, Session $session)
    {
        return $data !== null
            ? sprintf('"%s"', $this->checkData($data)->format('%Y years %M months %D days %H:%i:%S'))
            : null
            ;
    }

    /**
     * checkData
     *
     * Check if Data is a DateInterval. If not, it tries to instanciate a
     * DateInterval with the given data.
     *
     * @access protected
     * @param  mixed $data
     * @throw \InvalidParameterException
     * @return DateInterval $data
     */
    protected function checkData($data)
    {
        if (!$data instanceof \DateInterval) {
            try {
                $data = new \DateInterval($data);
            } catch (\Exception $e) {
                throw new \InvalidParameterException(sprintf("First argument is not a \DateInterval instance."), null, $e);
            }
        }

        return $data;
    }
}
