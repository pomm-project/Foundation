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

use PommProject\Foundation\ParameterHolder;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Converter\ConverterHolder;
use PommProject\Foundation\Converter;

/**
 * DatabaseConfiguration
 *
 * Holds configuration related to a database for connections. It sets default
 * values for the following settings:
 *
 * default_client_poolers
 * configuration
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class DatabaseConfiguration
{
    protected $name;
    protected $parameter_holder = [];
    protected $converter_holder;

    /**
     * __construct
     *
     * DatabaseConfiguration constructor.
     *
     * @access public
     * @param  string $name
     * @param  array $configuration
     * @return void
     */
    public function __construct($name, array $configuration = [])
    {
        $this->parameter_holder = new ParameterHolder($configuration);
        $this->converter_holder = new ConverterHolder();
        $this->name             = $name;

        $this->initialize();
    }

    /**
     * getName
     *
     * Return the name of this configuration setting.
     * This name is used to generate the namespaces for the Model files.
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
            ->setDefaultValue('configuration', [])
            ->setDefaultValue('default_client_poolers', [
                'query' => '\PommProject\Foundation\Query\QueryPooler'
            ])
            ;

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
     * @return DatabaseConfig $this
     */
    protected function registerBaseConverters()
    {
        $this->getConverterHolder()
            ->registerConverter('Array', new Converter\PgArray($this->getConverterHolder()), [])
            ->registerConverter('Boolean', new Converter\PgBoolean(), ['bool'])
            ->registerConverter('Number', new Converter\PgNumber(), ['int2', 'int4', 'int8', 'numeric', 'float4', 'float8'])
            ->registerConverter('String', new Converter\PgString(), ['varchar', 'char', 'text', 'uuid', 'tsvector', 'xml', 'bpchar', 'name'])
            //->registerConverter('Timestamp', new Converter\PgTimestamp(), ['timestamp', 'date', 'time', 'timestamptz'])
            //->registerConverter('Interval', new Converter\PgIntervalISO8601(), ['interval'])
            //->registerConverter('Binary', new Converter\PgBytea(), ['bytea'])
            //->registerConverter('NumberRange', new Converter\PgNumberRange(), ['int4range', 'int8range', 'numrange'])
            //->registerConverter('TsRange', new Converter\PgTsRange(), ['tsrange', 'daterange'])
            //->registerConverter('JSON', new Converter\PgJSON(), ['json', 'jsonb'])
            ;

        return $this;
    }
}
