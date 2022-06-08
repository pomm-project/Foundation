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

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Exception\FoundationException;

/**
 * ConverterClient
 *
 * Converter wrapper as Session's client.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       Client
 */
class ConverterClient extends Client
{
    /**
     * __construct
     *
     * Wrap the given converter.
     *
     * @access public
     * @param  string    $name
     * @param  ConverterInterface $converter
     */
    public function __construct(protected string $name, protected ConverterInterface $converter)
    {
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType(): string
    {
        return 'converter';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier(): string
    {
        return $this->name;
    }

    /**
     * toPg
     *
     * Trigger converter's toPg conversion method.
     *
     * @access public
     * @param mixed $value
     * @param string|null $type
     * @return string
     * @throws FoundationException
     * @see ConverterInterface
     */
    public function toPg(mixed $value, ?string $type = null): string
    {
        return $this->converter->toPg(
            $value,
            $type ?? $this->getClientIdentifier(),
            $this->getSession()
        );
    }

    /**
     * fromPg
     *
     * Trigger converter's fromPg conversion method.
     *
     * @access public
     * @param mixed $value
     * @param string|null $type
     * @return mixed
     * @throws FoundationException
     * @see ConverterInterface
     */
    public function fromPg(mixed $value, ?string $type = null): mixed
    {
        return $this->converter->fromPg(
            $value,
            $type ?? $this->getClientIdentifier(),
            $this->getSession()
        );
    }

    /**
     * toPgStandardFormat
     *
     * Export data as CSV representation
     *
     * @access public
     * @param mixed $value
     * @param string|null $type
     * @return string
     * @throws FoundationException
     * @see ConverterInterface
     */
    public function toPgStandardFormat(mixed $value, ?string $type = null): string
    {
        return $this->converter->toPgStandardFormat(
            $value,
            $type ?? $this->getClientIdentifier(),
            $this->getSession()
        );
    }

    /**
     * getConverter
     *
     * Return the embedded converter.
     *
     * @access public
     * @return ConverterInterface
     */
    public function getConverter(): ConverterInterface
    {
        return $this->converter;
    }
}
