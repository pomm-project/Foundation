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
 * PHP type for PostgreSQL's point type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Point implements \Stringable
{
    public float $x;
    public float $y;

    /**
     * __construct
     *
     * Create a point from a string description.
     *
     * @access public
     * @param  string $description
     */
    public function __construct(string $description)
    {
        $description = trim($description, ' ()');

        if (!preg_match('/([0-9e\-+\.]+), *([0-9e\-+\.]+)/', $description, $matches)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Could not parse point representation '%s'.",
                    $description
                )
            );
        }

        $this->x = (float) $matches[1];
        $this->y = (float) $matches[2];
    }

    /**
     * __toString
     *
     * Return a string representation of Point.
     *
     * @access public
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "(%s,%s)",
            $this->x,
            $this->y
        );
    }
}
