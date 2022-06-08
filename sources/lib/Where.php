<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2011 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Where implements \Stringable
{
    public array $stack = [];
    public $element;
    public array $values = [];
    public $operator;

    /**
     * create
     *
     * A constructor you can chain from.
     *
     * @static
     * @access public
     * @param string|null $element
     * @param  array  $values
     * @return Where
     */
    public static function create(string $element = null, array $values = []): Where
    {
        return new self($element, $values);
    }

    /**
     * createWhereIn
     *
     * Create an escaped IN clause.
     *
     * @access public
     * @param string $element
     * @param  array  $values
     * @return Where
     */
    public static function createWhereIn(string $element, array $values): Where
    {
        return self::createGroupCondition($element, 'IN', $values);
    }

    /**
     * createWhereNotIn
     *
     * Create an escaped NOT IN clause.
     *
     * @access public
     * @param string $element
     * @param  array $values
     * @return Where
     */
    public static function createWhereNotIn(string $element, array $values): Where
    {
        return self::createGroupCondition($element, 'NOT IN', $values);
    }

    /**
     * createGroupCondition
     *
     * Create a Where instance with multiple escaped parameters. This is mainly
     * useful for IN or NOT IN clauses.
     *
     * @access public
     * @param string $element
     * @param string $operation
     * @param  array  $values
     * @return Where
     */
    public static function createGroupCondition(string $element, string $operation, array $values): Where
    {
        return new self(
            sprintf(
                "%s %s (%s)",
                $element,
                $operation,
                join(", ", static::escapeSet($values))
            ),
            static::extractValues($values)
        );
    }

    /**
     * extractValues
     *
     * Extract values with consistent keys.
     *
     * @access protected
     * @param  array $values
     * @return array
     */
    protected static function extractValues(array $values): array
    {
        $array = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($values)) as $value) {
            $array[] = $value;
        }

        return $array;
    }

    /**
     * escapeSet
     *
     * Create an array of escaped strings from a value set.
     *
     * @access protected
     * @param  array $values
     * @return array
     */
    protected static function escapeSet(array $values): array
    {
        $escaped_values = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                $escaped_values[] =
                    sprintf("(%s)", join(', ', static::escapeSet($value)));
            } else {
                $escaped_values[] = '$*';
            }
        }

        return $escaped_values;
    }

    /**
     * __construct
     *
     * @access public
     * @param string|null $element (optional)
     * @param array  $values  (optional)
     */
    public function __construct(string $element = null, array $values = [])
    {
        if ($element !== null) {
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
     * @param string $operator
     * @return Where
     */
    public function setOperator(string $operator): Where
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
    public function isEmpty(): bool
    {
        return $this->element === null && count($this->stack) == 0;
    }

    /**
     * transmute
     *
     * Absorbing another Where instance.
     *
     * @access private
     * @param Where $where
     * @return void $this
     */
    private function transmute(Where $where): void
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
     * @param string $operator
     * @return Where
     */
    public function addWhere(mixed $element, array $values, string $operator): Where
    {
        if (!$element instanceof Where) {
            $element = new self($element, $values);
        }

        if ($element->isEmpty()) {
            return $this;
        }

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
    public function andWhere(mixed $element, array $values = []): Where
    {
        return $this->addWhere($element, $values, 'AND');
    }

    /**
     * orWhere
     *
     * @access public
     * @param  mixed $element
     * @return Where
     */
    public function orWhere(mixed $element, array $values = []): Where
    {
        return $this->addWhere($element, $values, 'OR');
    }

    /**
     * setStack
     *
     * @access public
     * @param array $stack
     * @return Where
     */
    public function setStack(array $stack): Where
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
    public function __toString(): string
    {
        return $this->isEmpty() ? 'true' : $this->parse();
    }

    /**
     * hasElement
     *
     * @access public
     * @return boolean
     */
    public function hasElement(): bool
    {
        return $this->element !== null;
    }

    /**
     * getElement
     *
     * @access public
     * @return string
     */
    public function getElement(): string
    {
        return $this->element;
    }

    /**
     * parse
     *
     * @access protected
     * @return string
     */
    protected function parse(): string
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
    public function getValues(): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        if ($this->hasElement()) {
            return $this->values;
        }

        $values = [];

        foreach ($this->stack as $where) {
            $values[] = $where->getValues();
        }

        return call_user_func_array('array_merge', $values);
    }
}
