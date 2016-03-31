<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Session\ResultHandler;

/**
 * ResultIteratorInterface
 *
 * An interface to describe Pomm result iterators.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface ResultIteratorInterface
{
    /**
     * isEmpty
     *
     * Is the collection empty (no element) ?
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * isEven
     *
     * Is the iterator on an even position ?
     *
     * @return boolean
     */
    public function isEven();

    /**
     * isOdd
     *
     * Is the iterator on an odd position ?
     *
     * @return boolean
     */
    public function isOdd();

    /**
     * getOddEven
     *
     * Return 'odd' or 'even' depending on the element index position.
     * Useful to style list elements when printing lists to do
     * <li class="line_<?php $list->getOddEven() ?>">.
     *
     * @return String
     */
    public function getOddEven();

    /**
     * slice
     *
     * Extract an array of values for one column.
     *
     * @param  string $field
     * @return array  values
     */
    public function slice($field);

    /**
     * extract
     *
     * Dump an iterator.
     * This actually stores all the results in PHP allocated memory.
     * THIS MAY USE A LOT OF MEMORY.
     *
     * @access public
     * @return array
     */
    public function extract();
}
