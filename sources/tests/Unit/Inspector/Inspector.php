<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Inspector;

use PommProject\Foundation\Inspector\InspectorPooler;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Query\QueryPooler;
use PommProject\Foundation\Test\Unit\SessionAwareAtoum;

class Inspector extends SessionAwareAtoum
{
    protected function registerClientPoolers()
    {
        $this->session
            ->registerClientPooler(new QueryPooler())
            ->registerClientPooler(new InspectorPooler())
            ->registerClientPooler(new ConverterPooler())
            ;
    }

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector();
    }

    public function testGetTableOid()
    {
        $this
            ->integer($this->getInspector()->getTableOid('pg_catalog', 'pg_class'))
            ->variable($this->getInspector()->getTableOid('no schema', 'no table'))
            ->isNull()
            ;
    }

    public function testGetTableFieldInformation()
    {
        $oid = $this
            ->getInspector()
            ->getTableOid('pg_catalog', 'pg_class')
            ;
        $this
            ->array($this->getInspector()->getTableFieldInformation($oid))
            ->hasKeys(['attname', 'type', 'type_namespace', 'defaultval', 'notnull', 'index'])
            ;
    }
}
