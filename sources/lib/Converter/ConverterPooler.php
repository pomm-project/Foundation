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

use PommProject\Foundation\Client\ClientPooler;
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
class ConverterPooler extends ClientPooler
{
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
        parent::register($session);
        $converter_holder = $session->getDatabaseConfiguration()->getConverterHolder();
        $types = $converter_holder->getTypesWithConverterName();

        foreach($types as $type => $name) {
            $this->getSession()->registerClient(new ConverterClient($type, $converter_holder->getConverter($name)));
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
        $client = $this->getSession()->getClient($this->getPoolerType(), $name);

        if ($client === null) {
            throw new ConverterException(sprintf("No converter registered as '%s'.", $name));
        }

        return $client;
    }
}
