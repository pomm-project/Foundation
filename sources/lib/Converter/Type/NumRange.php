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

class NumRange
{
    public $start_limit;
    public $end_limit;
    public $start_incl;
    public $end_incl;

    protected $description;

    public function __construct($description, $type = null)
    {
        if (!preg_match('/([\[\(])(-?[0-9\.]+), *(-?[0-9\.]+)([\]\)])/', $description, $matchs)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Could not parse NumRange description '%s'.",
                    $description
                )
            );
        }

        $this->start_limit = $matchs[2] + 0;
        $this->end_limit   = $matchs[3] + 0;
        $this->start_incl  = (bool) ($matchs[1] === '[');
        $this->end_incl    = (bool) ($matchs[4] === ']');
        $this->description = $description;
    }

    /**
     * __toString
     *
     * Text representation of a range.
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return sprintf("'%s'", $this->description);
    }
}
