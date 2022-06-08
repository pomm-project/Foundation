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
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Session\Session;

/**
 * ArrayTypeConverter
 *
 * Array sub class for converters using a PHP array representation.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 * @abstract
 */
abstract class ArrayTypeConverter implements ConverterInterface
{
    protected array $converters = [];

    /**
     * checkArray
     *
     * Check if the data is an array.
     *
     * @access protected
     * @param  mixed    $data
     * @throws ConverterException
     * @return array    $data
     */
    protected function checkArray(mixed $data): array
    {
        if (!is_array($data)) {
            throw new ConverterException(
                sprintf(
                    "Array converter data must be an array ('%s' given).",
                    gettype($data)
                )
            );
        }

        return $data;
    }

    /**
     * getSubtypeConverter
     *
     * Since the arrays in PostgreSQL have the same subtype, it is useful to
     * cache it here to avoid summoning the ClientHolder all the time.
     *
     * @access protected
     * @param string $type
     * @param Session $session
     * @return ConverterInterface
     * @throws FoundationException
     */
    protected function getSubtypeConverter(string $type, Session $session): ConverterInterface
    {
        if (!isset($this->converters[$type])) {
            /** @var ConverterClient $converterClient */
            $converterClient = $session->getClientUsingPooler('converter', $type);
            $this->converters[$type] = $converterClient->getConverter();
        }

        return $this->converters[$type];
    }
}
