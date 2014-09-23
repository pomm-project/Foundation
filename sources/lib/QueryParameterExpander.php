<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

/**
 * QueryParameterExpander
 *
 * Convert ordered query parameter lists from/to unordered lists.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class QueryParameterExpander
{
    /**
     * unorder
     *
     * Transform an ordered parameters list with $1, $2 to $* parameters.
     *
     * @static
     * @access public
     * @param  mixed $string
     * @return string
     */
    public static function unorder($string)
    {
        return preg_replace('/\$[0-9]+/', '$*', $string);
    }

    /**
     * order
     *
     * Transform an unordered parameters list $* to ordered $1, $2 parameters.
     *
     * @static
     * @access public
     * @param  mixed $string
     * @return string
     */
    public static function order($string)
    {
        return preg_replace_callback('/\$\*/', function () { static $nb = 0; return sprintf("$%d", ++$nb); }, $string );
    }
}
