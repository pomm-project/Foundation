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

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Session;

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
        if (!$data instanceOf \DateInterval) {
            if ($data === null) {
                return sprintf("NULL::%s", $type);
            } else {
                throw new ConverterException(sprintf("First argument is not a \DateInterval instance."));
            }
        }

        return sprintf("%s '%s'", $type, $data->format('%Y years %M months %D days %H:%i:%S'));
    }
}
