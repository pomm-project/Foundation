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
use PommProject\Foundation\Session\Session as BaseSession;

/**
 * ConvertedResultIterator
 *
 * Iterator on converted results.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ResultIterator
 */
class ConvertedResultIterator extends ResultIterator
{
    protected array $types = [];
    protected array $converters = [];

    /**
     * @param ResultHandler $result
     * @param BaseSession   $session
     */
    public function __construct(ResultHandler $result, protected BaseSession $session)
    {
        parent::__construct($result);
        $this->initTypes();
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
    public function get(int $index): array
    {
        return $this->parseRow(parent::get($index));
    }

    /**
     * initTypes
     *
     * Get the result types from the result handler.
     *
     * @access protected
     */
    protected function initTypes(): ResultIterator
    {
        foreach ($this->result->getFieldNames() as $name) {
            $type = $this->result->getFieldType($name);

            if ($type === null) {
                $type = 'text';
            }

            $this->types[$name] = $type;
            $this->converters[$name] = $this
                ->session
                ->getClientUsingPooler('converter', $type)
            ;
        }

        return $this;
    }

    /**
     * parseRow
     *
     * Convert values from Pg.
     *
     * @access protected
     */
    protected function parseRow(array $values): array
    {
        $output_values = [];

        foreach ($values as $name => $value) {
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
     */
    protected function convertField(string $name, ?string $value): mixed
    {
        return $this
            ->converters[$name]
            ->fromPg($value, $this->types[$name])
            ;
    }

    /**
     * slice
     *
     * see @ResultIterator
     */
    public function slice(string $field): array
    {
        $values = [];
        foreach (parent::slice($field) as $value) {
            $values[] = $this->convertField($field, $value);
        }

        return $values;
    }
}
