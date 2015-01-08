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

use PommProject\Foundation\Client\Client;

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
class ConverterClient extends Client
{
    protected $converter;
    protected $name;

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
    public function toPg($value, $type = null)
    {
        return $this->converter->toPg(
            $value,
            $type === null ? $this->getClientIdentifier() : $type,
            $this->getSession()
        );
    }

    /**
     * fromPg
     *
     * Trigger converter's fromPg conversion method.
     *
     * @access public
     * @param  mixed  $value
     * @param  string $type
     * @return mixed
     * @see ConverterInterface
     */
    public function fromPg($value, $type = null)
    {
        return $this->converter->fromPg(
            $value,
            $type === null ? $this->getClientIdentifier() : $type,
            $this->getSession()
        );
    }

    /**
     * toCsv
     *
     * Export data as CSV representation
     *
     * @access public
     * @param  mixed    $value
     * @param  string   $type
     * @return string
     * @see ConverterInterface
     */
    public function toCsv($value, $type = null)
    {
        return $this->converter->toCsv(
            $value,
            $type === null ? $this->getClientIdentifier() : $type,
            $this->getSession()
        );
    }

    /**
     * getConverter
     *
     * Return the embeded converter.
     *
     * @access public
     * @return ConverterInterface
     */
    public function getConverter()
    {
        return $this->converter;
    }
}
