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
 * Pager
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   MIT/X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Pager
{
    /**
     * __construct
     *
     * @access public
     * @param ResultIterator $iterator
     * @param int $count        Total number of results.
     * @param int $max_per_page Results per page
     * @param int $page         Page index.
     */
    public function __construct(protected ResultIterator $iterator, protected int $count, protected int $max_per_page, protected int $page)
    {
    }

    /**
     * getIterator
     *
     * Return the Pager's iterator.
     *
     * @access public
     * @return ResultIterator
     */
    public function getIterator(): ResultIterator
    {
        return $this->iterator;
    }

    /**
     * getResultCount
     *
     * Get the number of results in this page.
     *
     * @access public
     * @return int
     */
    public function getResultCount(): int
    {
        return $this->count;
    }

    /**
     * getResultMin
     *
     * Get the index of the first element of this page.
     *
     * @access public
     * @return int
     */
    public function getResultMin(): int
    {
        return min((1 + $this->max_per_page * ($this->page - 1)), $this->count);
    }

    /**
     * getResultMax
     *
     * Get the index of the last element of this page.
     *
     * @access public
     * @return int
     */
    public function getResultMax(): int
    {
        return max(($this->getResultMin() + $this->iterator->count() - 1), 0);
    }

    /**
     * getLastPage
     *
     * Get the last page index.
     *
     * @access public
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->count == 0 ? 1 : ceil($this->count / $this->max_per_page);
    }

    /**
     * getPage
     *
     * @access public
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * isNextPage
     *
     * True if a next page exists.
     *
     * @access public
     * @return Boolean
     */
    public function isNextPage(): bool
    {
        return $this->getPage() < $this->getLastPage();
    }

    /**
     * isPreviousPage
     *
     * True if a previous page exists.
     *
     * @access public
     * @return Boolean
     */
    public function isPreviousPage(): bool
    {
        return $this->page > 1;
    }

    /**
     * getCount
     *
     * Get the total number of results in all pages.
     *
     * @access public
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * getMaxPerPage
     *
     * Get maximum result per page.
     *
     * @access public
     * @return int
     */
    public function getMaxPerPage(): int
    {
        return $this->max_per_page;
    }
}
