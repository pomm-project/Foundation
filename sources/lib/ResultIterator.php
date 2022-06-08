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
 * ResultIterator
 *
 * Iterator on database results.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       \Iterator
 * @see       \Countable
 * @see       \JsonSerializable
 */
class ResultIterator implements \Iterator, \Countable, \JsonSerializable, \SeekableIterator
{
    private int $position = 0;
    private ?int $rows_count = null;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  ResultHandler $result
     */
    public function __construct(protected ResultHandler $result)
    {
    }

    /**
     * __destruct
     *
     * Closes the cursor when the collection is cleared.
     */
    public function __destruct()
    {
        $this->result->free();
    }

    /**
     * seek
     *
     * Alias for get(), required to be a Seekable iterator.
     */
    public function seek(int $offset): void
    {
        $this->get($offset); // throw exception if out of bounds
    }

    /**
     * get
     *
     * Return a particular result. An array with converted values is returned.
     * pg_fetch_array is muted because it produces untrappable warnings on
     * errors.
     *
     * @param  integer $index
     * @return mixed
     */
    public function get(int $index): mixed
    {
        return $this->result->fetchRow($index);
    }

    /**
     * has
     *
     * Return true if the given index exists false otherwise.
     *
     * @param integer $index
     * @return boolean
     */
    public function has(int $index): bool
    {
        return $index < $this->count();
    }

    /**
     * count
     *
     * @see    \Countable
     */
    public function count(): int
    {
        if ($this->rows_count == null) {
            $this->rows_count = $this->result->countRows();
        }

        return $this->rows_count;
    }

    /**
     * rewind
     *
     * @see \Iterator
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * current
     *
     * @see \Iterator
     */
    public function current(): mixed
    {
        return (($this->rows_count != null && $this->rows_count > 0 ) || !$this->isEmpty())
            ? $this->get($this->position)
            : null
            ;
    }

    /**
     * key
     *
     * @see \Iterator
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * next
     *
     * @see \Iterator
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * valid
     *
     * @see \Iterator
     * @return boolean
     */
    public function valid(): bool
    {
        return $this->has($this->position);
    }

    /**
     * isFirst
     * Is the iterator on the first element ?
     * Returns null if the iterator is empty.
     */
    public function isFirst(): ?bool
    {
        return !$this->isEmpty()
            ? $this->position === 0
            : null
            ;
    }

    /**
     * isLast
     *
     * Is the iterator on the last element ?
     * Returns null if the iterator is empty.
     */
    public function isLast(): ?bool
    {
        return !$this->isEmpty()
            ? $this->position === $this->count() - 1
            : null
            ;
    }

    /**
     * isEmpty
     *
     * Is the collection empty (no element) ?
     */
    public function isEmpty(): bool
    {
        return $this->rows_count === 0 || $this->count() === 0;
    }

    /**
     * isEven
     *
     * Is the iterator on an even position ?
     */
    public function isEven(): bool
    {
        return ($this->position % 2) === 0;
    }

    /**
     * isOdd
     *
     * Is the iterator on an odd position ?
     */
    public function isOdd(): bool
    {
        return ($this->position % 2) === 1;
    }

    /**
     * getOddEven
     *
     * Return 'odd' or 'even' depending on the element index position.
     * Useful to style list elements when printing lists to do
     * <li class="line_<?php $list->getOddEven() ?>">.
     */
    public function getOddEven(): string
    {
        return $this->position % 2 === 1 ? 'odd' : 'even';
    }

    /**
     * slice
     *
     * Extract an array of values for one column.
     */
    public function slice(string $field): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        return $this->result->fetchColumn($field);
    }

    /**
     * extract
     *
     * Dump an iterator.
     * This actually stores all the results in PHP allocated memory.
     * THIS MAY USE A LOT OF MEMORY.
     *
     * @access public
     */
    public function extract(): array
    {
        $results = [];

        foreach ($this as $result) {
            $results[] = $result;
        }

        return $results;
    }

    /**
     * jsonSerialize
     *
     * @see \JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return $this->extract();
    }
}
