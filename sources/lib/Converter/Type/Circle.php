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

use PommProject\Foundation\Converter\Type\Point;

/**
 * Circle
 *
 * PHP representation of Postgresql circle type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Circle
{
    public $center;
    public $radius;

    /**
     * __construct
     *
     * Create a circle from a description string.
     *
     * @access public
     * @param  string $description
     * @return void
     */
    public function __construct($description)
    {
        $description = trim($description, ' <>');
        $elts = preg_split('/[,\s]*(\([^\)]+\))[,\s]*|[,\s]+/', $description, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (count($elts) !== 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Could not parse circle description '%s'.",
                    $description
                )
            );
        }

        $this->center = $this->createPointFrom($elts[0]);
        $this->radius = (float) $elts[1];
    }

    /**
     * createPointFrom
     *
     * Create a point from a description.
     *
     * @access protected
     * @param  string $description
     * @return Point
     */
    protected function createPointFrom($description)
    {
        return new Point($description);
    }

    /**
     * __toString
     *
     * Create a string representation of the Cicle.
     * Actually, it dumps a SQL compatible circle representation.
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "circle(%s,%s)",
            $this->center->__toString(),
            $this->radius
        );
    }
}
