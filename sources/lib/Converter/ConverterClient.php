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
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Session;

/**
 * ConverterClient
 *
 * Converter wrapper as Session's client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see Client
 */
class ConverterClient implements ClientInterface
{
    protected $converter;
    protected $name;
    protected $session;

    /**
     * __construct
     *
     * Wrap the given converter.
     *
     * @access public
     * @param  string    $name
     * @param  Converter $converter
     * @return void
     */
    public function __construct($name, ConverterInterface $converter)
    {
        $this->name      = $name;
        $this->converter = $converter;
    }

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
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return $this->name;
    }

    /**
     * initialize
     *
     * @see ClientInterface
     */
    public function initialize(Session $session)
    {
        $this->session = $session;
    }

    /**
     * shutdown
     *
     * @see ClientInterface
     */
    public function shutdown()
    {
    }

    /**
     * toPg
     *
     * Trigger converter's toPg conversion method.
     *
     * @access public
     * @param  mixed  $value
     * @param  string $type
     * @return string
     * @see ConverterInterface
     */
    public function toPg($value, $type)
    {
        $this->checkInitialized();

        return $this->converter->toPg($value, $type, $this->session);
    }

    /**
     * fromPg
     *
     * Trigger converter's fromPg conversion method.
     *
     * @access public
     * @param  mixed $value
     * @param  string $type
     * @return mixed
     * @see ConverterInterface
     */
    public function fromPg($value, $type)
    {
        $this->checkInitialized();

        return $this->converter->fromPg($value, $type, $this->session);
    }

    /**
     * checkInitialized
     *
     * Throw an exception if session is not set.
     *
     * @access private
     * @return ConverterClient $this
     */
    private function checkInitialized()
    {
        if ($this->session === null) {
            throw new ConverterException(sprintf("Converter client '%s' is not initialized.", get_class($this)));
        }

        return $this;
    }
}
