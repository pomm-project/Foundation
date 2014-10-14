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

use PommProject\Foundation\Client\ClientInterface;

abstract class BaseConverter implements ConverterInterface, ClientInterface
{
    protected $session;

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'converter';
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function initialize(Session $session)
    {
        $this->session = $session;
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function shutdown()
    {
    }
}
