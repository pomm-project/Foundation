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

use PommProject\Foundation\Converter\ConverterHolder;

/**
 * DatabaseConfiguration
 *
 * Holds configuration related to a database for connections. It sets default
 * values for the following settings:
 *
 * default:client_poolers
 * configuration
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class DatabaseConfiguration
{
    protected $parameter_holder;
    protected $converter_holder;
    protected $name;

    /**
     * __construct
     *
     * DatabaseConfiguration constructor.
     *
     * @access public
     * @param  string $name
     * @param  array  $configuration
     * @return void
     */
    public function __construct(
        array $configuration = [],
        ParameterHolder $parameter_holder = null,
        ConverterHolder $converter_holder = null
    )
    {
        $this->parameter_holder = $parameter_holder === null ? new ParameterHolder($configuration) : $parameter_holder;
        $this->converter_holder = $converter_holder === null ? new ConverterHolder() : $converter_holder;

        $this->initialize();
    }

    /**
     * name
     *
     * Set or get the current configuration name.
     * If no parameters are passed it acts like a get. Otherwise the given name
     * is set. When no name have been set, it checks in the parameter holder or
     * simply the class name.
     *
     * @param  string $name (null)
     * @return mixed  name it this
     */
    public function name($name = null)
    {
        if ($name === null) {
            return $this->name === null
                ? $this->parameter_holder->getParameter('name', get_class($this))
                : $this->name
                ;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * getConverterHolder
     *
     * Return the converter holder.
     *
     * @access public
     * @return ConverterHolder
     */
    public function getConverterHolder()
    {
        return $this->converter_holder;
    }

    /**
     * getParameterHolder
     *
     * Return the parameter holder.
     *
     * @access public
     * @return ParameterHolder
     */
    public function getParameterHolder()
    {
        return $this->parameter_holder;
    }

    /**
     * initialize
     *
     * Set default values and load resources.
     *
     * @access protected
     * @return DatabaseConfiguration $this
     */
    protected function initialize()
    {
        $this->parameter_holder
            ->setDefaultValue(
                'configuration',
                [
                    'bytea_output'  => 'hex',
                    'intervalstyle' => 'ISO_8601',
                    'datestyle'     => 'ISO',
                ]
            )
            ->setDefaultValue(
                'default:client_poolers',
                [
                    'prepared_query'     => '\PommProject\Foundation\PreparedQuery\PreparedQueryPooler',
                    'query'              => '\PommProject\Foundation\Query\QueryPooler',
                    'converter'          => '\PommProject\Foundation\Converter\ConverterPooler',
                    'observer'           => '\PommProject\Foundation\Observer\ObserverPooler',
                ]
            );

        return $this
            ->registerBaseConverters()
            ;
    }

    /**
     * registerBaseConverters
     *
     * Register the converters for postgresql's built-in types
     *
     * @access protected
     * @return DatabaseConfiguration $this
     */
    protected function registerBaseConverters()
    {
        $this->getConverterHolder()
            ->registerConverter('Array', new Converter\PgArray(), ['array'])
            ->registerConverter('Boolean', new Converter\PgBoolean(), ['bool'])
            ->registerConverter(
                'Number',
                new Converter\PgNumber(),
                ['int2', 'int4', 'int8', 'numeric', 'float4', 'float8', 'oid']
            )
            ->registerConverter(
                'String',
                new Converter\PgString(),
                ['varchar', 'char', 'text', 'uuid', 'tsvector', 'xml', 'bpchar', 'name', 'character varying', 'regclass']
            )
            ->registerConverter(
                'Timestamp',
                new Converter\PgTimestamp(),
                ['timestamp', 'date', 'time', 'timestamptz']
            )
            ->registerConverter('Interval', new Converter\PgInterval(), ['interval'])
            ->registerConverter('Binary', new Converter\PgBytea(), ['bytea'])
            ->registerConverter('Point', new Converter\Geometry\PgPoint(), ['point'])
            ->registerConverter('Circle', new Converter\Geometry\PgCircle(), ['circle'])
            //->registerConverter('NumberRange', new Converter\PgNumberRange(), ['int4range', 'int8range', 'numrange'])
            //->registerConverter('TsRange', new Converter\PgTsRange(), ['tsrange', 'daterange'])
            //->registerConverter('JSON', new Converter\PgJSON(), ['json', 'jsonb'])
            ;

        return $this;
    }
}
