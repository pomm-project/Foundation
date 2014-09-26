<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;


/**
 * Where
 *
 * This class represents a WHERE clause of a SQL statement. It deals with AND &
 * OR operator you can add using handy methods. This allows you to build
 * queries dynamically.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Where
{
    public $stack = [];
    public $element;
    public $values = [];
    public $operator;

    /**
     * create
     *
     * A constructor you can chain from.
     *
     * @static
     * @access public
     * @param  string $element
     * @param  array  $values
     * @return Where
     */
    public static function create($element = null, array $values = [])
    {
        return new self($element, $values);
    }

    /**
     * createWhereIn
     *
     * Create an escaped IN clause.
     *
     * @static
     * @access public
     * @param  string $element
     * @param  array  $values
     * @return Where
     */
    public static function createWhereIn($element, array $values)
    {
        $escape = function ($values) use (&$escape) {
                $escaped_values = [];

                foreach($values as $value) {
                    if (is_array($value)) {
                        $escaped_values[] =
                            sprintf("(%s)", join(', ', $escape($value)));
                    } else {
                        $escaped_values[] = '$*';
                    }
                }

                return $escaped_values;
            };

        $get_values = function($values) {
                $array = [];

                foreach(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($values)) as $value) {
                    $array[] = $value;
                }

                return $array;
            };

        return new self(sprintf(
            "%s IN (%s)",
            $element, join(", ", $escape($values))),
            $get_values($values)
        );
    }

    /**
     * __construct
     *
     * @access public
     * @param string $element  (optional)
     * @param array  $values   (optional)
     */
    public function __construct($element = null, array $values = [])
    {
        if (!is_null($element)) {
            $this->element = $element;
            $this->values = $values;
        }
    }

    /**
     * setOperator
     *
     * is it an AND or an OR ?
     * or something else.
     * XOR can be expressed as "A = !B"
     *
     * @access public
     * @param  string $operator
     * @return Where
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * isEmpty
     *
     * is it a fresh brand new object ?
     *
     * @access public
     * @return boolean
     */
    public function isEmpty()
    {
        return (bool) (is_null($this->element) && count($this->stack) == 0);
    }

    /**
     * transmute
     *
     * Absorbing another Where instance.
     *
     * @access public
     * @param  Where $where
     * @return Where $this
     */
    public function transmute(Where $where)
    {
        $this->stack    = $where->stack;
        $this->element  = $where->element;
        $this->operator = $where->operator;
        $this->values   = $where->values;
    }

    /**
     * addWhere
     *
     * You can add a new WHERE clause with your own operator.
     *
     * @access public
     * @param  mixed  $element
     * @param  array  $values
     * @param  string $operator
     * @return Where
     */
    public function addWhere($element, array $values, $operator)
    {
        if (!$element instanceof Where) {
            $element = new self($element, $values);
        }

        if ($element->isEmpty()) return $this;
        if ($this->isEmpty()) {
            $this->transmute($element);

            return $this;
        }

        if ($this->hasElement()) {
            $this->stack = [new self($this->getElement(), $this->values), $element];
            $this->element = null;
            $this->values = [];
        } else {
            if ($this->operator == $operator) {
                $this->stack[] = $element;
            } else {
                $this->stack = [
                    self::create()
                        ->setStack($this->stack)
                        ->setOperator($this->operator),
                    $element
                ];
            }
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * andWhere
     *
     * Or use a ready to use AND where clause.
     *
     * @access public
     * @param  mixed $element
     * @param  array $values
     * @return Where
     */
    public function andWhere($element, array $values = [])
    {
        return $this->addWhere($element, $values, 'AND');
    }

    /**
     * orWhere
     *
     * @access public
     * @param  mixed $element
     * @param  array $values
     * @return Where
     */
    public function orWhere($element, array $values = [])
    {
        return $this->addWhere($element, $values, 'OR');
    }

    /**
     * setStack
     *
     * @access public
     * @param  array $stack
     * @return Where
     */
    public function setStack(array $stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * __toString
     *
     * where your SQL statement is built.
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->isEmpty() ? 'true' : $this->parse();
    }

    /**
     * hasElement
     *
     * @access public
     * @return boolean
     */
    public function hasElement()
    {
        return ! is_null($this->element);
    }

    /**
     * getElement
     *
     * @access public
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * parse
     *
     * @access protected
     * @return string
     */
    protected function parse()
    {
        if ($this->hasElement()) {
            return $this->getElement();
        }

        $stack = [];
        foreach ($this->stack as $offset => $where) {
            $stack[$offset] = $where->parse();
        }

        return sprintf('(%s)', join(sprintf(' %s ', $this->operator), $stack));
    }

    /**
     * getValues
     *
     * Get all the values back for the prepared statement.
     *
     * @access public
     * @return array
     */
    public function getValues()
    {
        if ($this->isEmpty()) {
            return [];
        }

        if ($this->hasElement()) {
            return $this->values;
        }

        $values = [];

        foreach($this->stack as $offset => $where) {
            $values = array_merge($values, $where->getValues());
        }

        return $values;
    }
}
