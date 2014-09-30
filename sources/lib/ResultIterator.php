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
    protected $result_resource;
    protected $types = [];
    protected $session;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  resource $result_resource
     * @param  Session  $session
     * @return void
     */
    public function __construct($result_resource, Session $session)
    {
        $this->result_resource  = $result_resource;
        $this->session          = $session;
        $this->position         = $this->result_resource === false ? null : 0;
        $this->getTypes();
    }

    /**
     * __destruct
     *
     * Closes the cursor when the collection is cleared.
     */
    public function __destruct()
    {
        @pg_free_result($this->result_resource);
    }

    /**
     * get
     *
     * Return a particular result. If the result cannot be read, false is
     * returned, otherwise, the according FlexibleEntity is returned.
     * pg_fetch_array is muted because it produces untrappable warnings on
     * errors.
     *
     * @param  integer $index
     * @return array
     */
    public function get($index)
    {
        $values = @pg_fetch_array($this->result_resource, $index, \PGSQL_NUM);

        if ($values === false) {
            throw new \OutOfBoundsException(sprintf("Cannot jump to non existing row %d.", $index));
        }

        return $this->parseRow($values);
    }

    /**
     * parseRow
     *
     * Convert values from Pg.
     *
     * @access protected
     * @param  array $values
     * @return array
     */
    protected function parseRow(array $values)
    {
        $output_values = [];

        foreach($values as $index => $value) {
            $output_values[pg_field_name($this->result_resource, $index)] =
                $this->convertField($index, $value) ;
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
    protected function convertField($field_no, $value)
    {
        $type = $this->getFieldType($field_no);

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
                ->fromPg($value, $type)
                ;
        }
    }

    /**
     * getFieldType
     *
     * Return the associated type of a field.
     *
     * @access protected
     * @param  int       $field_no
     * @return string
     */
    protected function getFieldType($field_no)
    {
        $type = pg_field_type($this->result_resource, $field_no);

        return $type !== 'unknown' ? $type : null;
    }

    /**
     * getFieldName
     *
     * Return the name from a field number.
     *
     * @access protected
     * @param  int       $field_no
     * @return string
     */
    protected function getFieldName($field_no)
    {
        return pg_field_name($this->result_resource, $field_no);
    }

    /**
     * getTypes
     *
     * Get the result types from the result handler.
     *
     * @access protectd
     * @return ResultIterator $this
     */
    protected function getTypes()
    {
        for($i = 0; $i < pg_num_fields($this->result_resource); $i++) {
            $this->types[$i] = pg_field_type($this->result_resource, $i);
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
        return pg_num_rows($this->result_resource);
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
        return $this->get($this->position);
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
        return pg_num_rows($this->result_resource) === 0;
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

        $field_no = pg_field_num($this->result_resource, $field);

        if ($field_no === -1) {
            throw new \InvalidArgumentException(sprintf("No such field '%s' in result set.", $field));
        }

        $values = [];

        foreach(pg_fetch_all_columns($this->result_resource, $field_no) as $incoming_value) {
            $values[] = $this->convertField($field_no, $incoming_value);
        }

        return $values;
    }
}
