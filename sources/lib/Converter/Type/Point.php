<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter\Type;

/**
 * Point
 *
 * PHP type for postgresql's point type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Point
{
    public $x;
    public $y;

    /**
     * __construct
     *
     * Set point's coordinates.
     *
     * @access public
     * @param  float $x
     * @param  float $y
     * @return void
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
