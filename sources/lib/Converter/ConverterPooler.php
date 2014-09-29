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

use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Converter\ConverterClient;
use PommProject\Foundation\Session;

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
class ConverterPooler implements ClientPoolerInterface
{
    protected $types = [];
    protected $session;

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
     * register
     *
     * @see ClientPoolerInterface
     */
    public function register(Session $session)
    {
        $this->session = $session;
        $converter_holder = $session->getDatabaseConfiguration()->getConverterHolder();
        $this->types = $converter_holder->getTypesWithConverterName();

        foreach($this->types as $type => $name) {
            $this->session->registerClient(new ConverterClient($name, $converter_holder->getConverter($name)));
        }
    }

    /**
     * getClient
     *
     * @see ClientPoolerInterface
     * @throw ConverterException if no clients found.
     */
    public function getClient($name)
    {
        $client = $this->session->getClient($this->getPoolerType(), $name);

        if ($client === null) {
            throw new ConverterException(sprintf("No converter registered as '%s'.", $name));
        }

        return $client;
    }

    /**
     * getConverterForType
     *
     * Return the converter client associated with the given type.
     *
     * @access public
     * @param  string $type
     * @return ConverterClient
     */
    public function getConverterForType($type)
    {
        if (!isset($this->types[$type])) {
            throw new ConverterException(sprintf("Type '%s' is not registered.", $type));
        }

        $client = $this->session->getClient($this->getPoolerType(), $this->types[$type]);

        if ($client === null) {
            throw new ConverterException(sprintf("No converter for type '%s'.", $type));
        }

        return $client;
    }
}
