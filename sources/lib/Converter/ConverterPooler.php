<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Exception\ConverterException;

/**
 * ConverterPooler
 *
 * Pooler for converters.
 *
 * @package     Foundation
 * @copyright   2014 - 2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see         ClientPooler
 */
class ConverterPooler extends ClientPooler
{
    /**
     * __construct
     *
     * Instantiate converter pooler.
     *
     * @access public
     * @param  ConverterHolder $converter_holder
     */
    public function __construct(protected ConverterHolder $converter_holder)
    {
    }

    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType(): string
    {
        return 'converter';
    }

    /**
     * getClient
     *
     * @see ClientPoolerInterface
     */
    public function getClient($identifier): ClientInterface
    {
        if ($identifier !== PgArray::getSubType($identifier)) {
            return parent::getClient('array');
        } else {
            return parent::getClient($identifier);
        }
    }

    /**
     * createClient
     *
     * Check in the converter holder if the type has an associated converter.
     * If not, an exception is thrown.
     *
     * @see   ClientPooler
     * @throws ConverterException
     */
    public function createClient(string $identifier): ConverterClient
    {
        if (!$this->converter_holder->hasType($identifier)) {
            throw new ConverterException(
                sprintf(
                    "No converter registered for type '%s'.",
                    $identifier
                )
            );
        }

        return new ConverterClient(
            $identifier,
            $this->converter_holder->getConverterForType($identifier)
        );
    }

    /**
     * getConverterHolder
     *
     * Expose converter holder so one can add new converters on the fly.
     *
     * @access public
     * @return ConverterHolder
     */
    public function getConverterHolder(): ConverterHolder
    {
        return $this->converter_holder;
    }
}
