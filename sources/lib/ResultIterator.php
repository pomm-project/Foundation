<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Exception\ConnectionException;
use PommProject\Foundation\Exception\FoundationException;

/**
 * ResultIterator
 *
 * Iterator on database results.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see \Iterator
 * @see \Countable
 */
class ResultIterator implements \Iterator, \Countable
{
    private   $position;
    protected $result;
    protected $types = [];
    protected $session;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  ResultHandler $result
     * @param  Session       $session
     * @return void
     */
    public function __construct(ResultHandler $result, Session $session)
    {
        $this->result   = $result;
        $this->session  = $session;
        $this->position = 0;
        $this->initTypes();
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
     * get
     *
     * Return a particular result. An array with converted values is returned.
     * pg_fetch_array is muted because it produces untrappable warnings on
     * errors.
     *
     * @param  integer $index
     * @return array
     */
    public function get($index)
    {
        return $this->parseRow($this->result->fetchRow($index));
    }

    /**
     * parseRow
     *
     * Convert values from Pg.
     *
     * @access protected
     * @param  array $values
     * @return mixed
     */
    protected function parseRow(array $values)
    {
        $output_values = [];

        foreach($values as $name => $value) {
            $output_values[$name] =
                $this->convertField($name, $value) ;
        }

        return $output_values;
    }

    /**
     * convertField
     *
     * Return converted value for a result field.
     *
     * @access protected
     * @param int    $field_no
     * @param string $value
     * @return mixed
     */
    protected function convertField($name, $value)
    {
        $type = $this->getFieldType($name);

        if (preg_match('/^_(.+)$/', $type, $matchs)) {

            return $this
                ->session
                ->getClientUsingPooler('converter', 'array')
                ->fromPg($value, $matchs[1])
            ;
        } else {
            if ($type === null) {
                $type = 'text';
            }

            return $this
                ->session
                ->getClientUsingPooler('converter', $type)
                ->fromPg($value)
                ;
        }
    }

    /**
     * getFieldType
     *
     * Method that can be overrided to determine field's type.
     *
     * @access protected
     * @param  string    $name
     * @return string
     */
    protected function getFieldType($name)
    {
        return $this->result->getFieldType($name);
    }


    /**
     * initTypes
     *
     * Get the result types from the result handler.
     *
     * @access protectd
     * @return ResultIterator $this
     */
    protected function initTypes()
    {
        foreach($this->result->getFieldNames() as $index => $name) {
            $this->types[$index] = $this->getFieldType($name);
        }

        return $this;
    }

    /**
     * has
     *
     * Return true if the given index exists false otherwise.
     *
     * @param  integer $index
     * @return boolean
     */
    public function has($index)
    {
        return (bool) ($index < $this->count());
    }

    /**
     * count
     *
     * @see \Countable
     * @return integer
     */
    public function count()
    {
        return $this->result->countRows();
    }

    /**
     * rewind
     *
     * @see \Iterator
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * current
     *
     * @see \Iterator
     */
    public function current()
    {
        return $this->isEmpty() ? null : $this->get($this->position);
    }

    /**
     * key
     *
     * @see \Iterator
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * next
     *
     * @see \Iterator
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * valid
     *
     * @see \Iterator
     * @return Boolean
     */
    public function valid()
    {
        return $this->has($this->position);
    }

    /**
     * isFirst
     * Is the iterator on the first element ?
     *
     * @return Boolean
     */
    public function isFirst()
    {
        return $this->position === 0;
    }

    /**
     * isLast
     *
     * Is the iterator on the last element ?
     *
     * @return Boolean
     */
    public function isLast()
    {
        return $this->position === $this->count() - 1;
    }

    /**
     * isEmpty
     *
     * Is the collection empty (no element) ?
     *
     * @return Boolean
     */
    public function isEmpty()
    {
        return $this->result->countRows() === 0;
    }

    /**
     * isEven
     *
     * Is the iterator on an even position ?
     *
     * @return Boolean
     */
    public function isEven()
    {
        return ($this->position % 2) === 0;
    }

    /**
     * isOdd
     *
     * Is the iterator on an odd position ?
     *
     * @return Boolean
     */
    public function isOdd()
    {
        return ($this->position % 2) === 1;
    }

    /**
     * getOddEven
     *
     * Return 'odd' or 'even' depending on the element index position.
     * Useful to style list elements when printing lists to do
     * <li class="line_<?php $list->getOddEven() ?>">.
     *
     * @return String
     */
    public function getOddEven()
    {
        return $this->position % 2 ? 'odd' : 'even';
    }

    /**
     * slice
     *
     * Extract an array of values for one column.
     *
     * @param  string $field
     * @return array  values
     */
    public function slice($field)
    {
        if ($this->isEmpty()) {
            return [];
        }

        $values = [];

        foreach($this->result->fetchColumn($field) as $incoming_value) {
            $values[] = $this->convertField($field, $incoming_value);
        }

        return $values;
    }
}
