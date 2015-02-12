<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter;

use PommProject\Foundation\Converter\PgArray;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Exception\ConverterException;

/**
 * ConverterPooler
 *
 * Pooler for converters.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class ConverterPooler extends ClientPooler
{
    protected $converter_holder;

    /**
     * __construct
     *
     * Instanciate converter pooler.
     *
     * @access public
     * @param  ConverterHolder $converter_holder
     * @return \PommProject\Foundation\Client\ClientPoolerInterface
     */
    public function __construct(ConverterHolder $converter_holder)
    {
        $this->converter_holder = $converter_holder;
    }

    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'converter';
    }

    /**
     * getClient
     *
     * @see ClientPoolerInterface
     */
    public function getClient($identifier)
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
     * @throw ConverterException
     */
    public function createClient($identifier)
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
     * Exposse converter holder so one can add new converters on the fly.
     *
     * @access public
     * @return ConverterHolder
     */
    public function getConverterHolder()
    {
        return $this->converter_holder;
    }
}
