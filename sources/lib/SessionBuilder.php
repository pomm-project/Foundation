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

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Session\SessionBuilder as VanillaSessionBuilder;
use PommProject\Foundation\Observer\ObserverPooler;
use PommProject\Foundation\Listener\ListenerPooler;
use PommProject\Foundation\Inspector\InspectorPooler;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Converter\ConverterHolder;
use PommProject\Foundation\QueryManager\QueryManagerPooler;
use PommProject\Foundation\PreparedQuery\PreparedQueryPooler;

/**
 * FoundationSessionBuilder
 *
 * Pre configured session builder.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see SessionBuilder
 */
class SessionBuilder extends VanillaSessionBuilder
{
    /**
     * postConfigure
     *
     * @see SessionBuilder
     */
    protected function postConfigure(Session $session)
    {
        $session
            ->registerClientPooler(new PreparedQueryPooler)
            ->registerClientPooler(new QueryManagerPooler)
            ->registerClientPooler(new ConverterPooler(clone $this->converter_holder))
            ->registerClientPooler(new ObserverPooler)
            ->registerClientPooler(new InspectorPooler)
            ->registerClientPooler(new ListenerPooler)
            ;

        return $this;
    }

    /**
     * initializeConverterHolder
     *
     * @see SessionBuilder
     */
    protected function initializeConverterHolder(ConverterHolder $converter_holder)
    {
        $converter_holder
            ->registerConverter('Array', new Converter\PgArray(), ['array'], false)
            ->registerConverter('Boolean', new Converter\PgBoolean(), ['bool'], false)
            ->registerConverter(
                'Number',
                new Converter\PgNumber(),
                ['int2', 'int4', 'int8', 'numeric', 'float4', 'float8', 'oid'],
                false
            )
            ->registerConverter(
                'String',
                new Converter\PgString(),
                [
                    'varchar',
                    'char',
                    'text',
                    'uuid',
                    'tsvector',
                    'xml',
                    'bpchar',
                    'name',
                    'character varying',
                    'regclass',
                    'inet',
                    'cidr',
                    'macaddr',
                ],
                false
            )
            ->registerConverter(
                'Timestamp',
                new Converter\PgTimestamp(),
                ['timestamp', 'date', 'time', 'timestamptz'],
                false
            )
            ->registerConverter('Interval', new Converter\PgInterval(), ['interval'], false)
            ->registerConverter('Binary', new Converter\PgBytea(), ['bytea'], false)
            ->registerConverter('Point', new Converter\Geometry\PgPoint(), ['point'], false)
            ->registerConverter('Circle', new Converter\Geometry\PgCircle(), ['circle'], false)
            ->registerConverter('JSON', new Converter\PgJson(), ['json', 'jsonb'], false)
            ->registerConverter('NumberRange', new Converter\PgNumRange(), ['int4range', 'int8range', 'numrange'], false)
            ->registerConverter('TsRange', new Converter\PgTsRange(), ['tsrange', 'daterange', 'tstzrange'], false)
            ;

        return $this;
    }
}
