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

/**
 * ResultIteratorInterface
 *
 * Interface for query result iterators.
 *
 * @package     Pomm
 * @copyright   2014 - 2016 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 */
interface ResultIteratorInterface extends \Countable, \SeekableIterator
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
     * get
     *
     * Return the result at the specified index.
     *
     * @param   int $index
     * @return  array
     */
    public function get($index);

    /**
     * has
     *
     * Return true if the given index exists false otherwise.
     *
     * @param  integer $index
     * @return boolean
     */
    public function has($index);
}
