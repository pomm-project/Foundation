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
 * BaseRange
 *
 * Abstract classes for range types.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
abstract class BaseRange
{
    public $start_limit;
    public $end_limit;
    public $start_incl;
    public $end_incl;

    protected $description;

    /**
     * getRegexp
     *
     * This function function must capture 4 elements of the description in
     * this order:
     *
     * - left bracket style
     * - left element
     * - right element
     * - right bracket style
     *
     * @return string
     */
    abstract protected function getRegexp();

    /**
     * getSubElement
     *
     * Return the representation for each element.
     *
     * @param  string $element
     * @return mixed
     */
    abstract protected function getSubElement($element);

    /**
     * __construct
     *
     * Create an instance from a string definition. This string definition
     * matches PostgreSQL range definition.
     *
     * @param  string $description
     * @throws \InvalidArgumentException
     */

    public function __construct($description)
    {
        if (!preg_match($this->getRegexp(), $description, $matches)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Could not parse NumRange description '%s'.",
                    $description
                )
            );
        }

        $this->start_limit = $this->getSubElement($matches[2]);
        $this->end_limit   = $this->getSubElement($matches[3]);
        $this->start_incl  = (bool) ($matches[1] === '[');
        $this->end_incl    = (bool) ($matches[4] === ']');
        $this->description = $description;
    }

    /**
     * __toString
     *
     * Text representation of a range.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->description;
    }
}
