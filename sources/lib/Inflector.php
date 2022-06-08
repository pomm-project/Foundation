<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

/**
 * Inflector
 *
 * Turn identifiers from/to StudlyCaps/underscore.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Inflector
{
    /**
     * Camelize a string.
     *
     * @static
     * @access public
     * @param string|null $string
     * @return string|null
     */
    public static function studlyCaps(?string $string = null): ?string
    {
        if ($string === null) {
            return null;
        }

        return preg_replace_callback(
            '/_([a-z])/',
            fn($v) => strtoupper((string) $v[1]),
            ucfirst(strtolower($string))
        );
    }

    /**
     * Underscore a string.
     *
     * @static
     * @access public
     * @param string|null $string
     * @return string|null
     */
    public static function underscore(string $string = null): ?string
    {
        if ($string === null) {
            return null;
        }

        return strtolower((string) preg_replace('/([A-Z])/', '_\1', lcfirst($string)));
    }
}
