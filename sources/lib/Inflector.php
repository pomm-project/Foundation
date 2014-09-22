<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Inflector
{
    /**
     * Camelize a string.
     *
     * @static
     * @access public
     * @param  string $string
     * @return string
     */
    public static function studlyCaps($string = null)
    {
        if ($string === null) {
            return null;
        }

        return preg_replace_callback('/_([a-z])/', function ($v) { return strtoupper($v[1]); }, ucfirst(strtolower($string)));
    }

    /**
     * Underscore a string.
     *
     * @static
     * @access public
     * @param  string $string
     * @return string
     */
    public static function underscore($string = null)
    {
        if ($string === null) {
            return null;
        }

        return strtolower(preg_replace('/([A-Z])/', '_\1', lcfirst($string)));
    }
}
