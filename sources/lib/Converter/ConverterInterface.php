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


/**
 * ConverterInterface
 *
 * Interface for converters.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface ConverterInterface
{
    /**
     * fromPg
     *
     * Parse the output string from Postgresql and returns the converted value
     * into an according PHP representation.
     *
     * @param  string $data Input string from Pg row result.
     * @param  string $type Optional type.
     * @return mixed  PHP representation of the data.
     */
    public function fromPg($data, $type);

    /**
     * toPg
     *
     * Convert a PHP representation into the according Pg formatted string.
     *
     * @param  mixed  $data PHP representation.
     * @param  string $type Optional type.
     * @return string Pg converted string for input.
     */
    public function toPg($data, $type);
}
