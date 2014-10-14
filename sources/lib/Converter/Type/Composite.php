<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter\Type;

use PommProject\Foundation\Exception\FoundationException;

/**
 * Composite
 *
 * Base class for all composite types.
 * It just needs to be extended and get public attributes.
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @abstract
 */
abstract class Composite
{
    /**
     * __construct
     *
     * The constructor takes all values as an array parameter. It checks if
     * the according attribute exists and set the value. If the attribute does
     * not exist, an exception is thrown.
     *
     * @access public
     * @param  array $data
     * @return void
     */
    public function __construct(array $data)
    {
        foreach ($values as $name => $value) {
            if (!property_exists($this, $name)) {
                throw new FoundationException(sprintf("Composite type '%s' does not have a '%s' attribute.", get_class($this), $name));
            }

            $this->$name = $value;
        }
    }
}
