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

use PommProject\Foundation\Session;
use PommProject\Foundation\Query\QueryPooler;
use PommProject\Foundation\Inspector\InspectorPooler;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Test\Unit\SessionAwareAtoum;
use PommProject\Foundation\Test\Fixture\InspectorFixture;
use PommProject\Foundation\Exception\FoundationException;

class Inspector extends SessionAwareAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClientPooler(new QueryPooler())
            ->registerClientPooler(new InspectorPooler())
            ->registerClientPooler(new ConverterPooler())
            ->registerClient(new InspectorFixture())
            ;
    }

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector();
    }

    protected function getFixture()
    {
        $fixture = $this
            ->getSession()
            ->getClient('fixture', 'inspector')
            ;

        if ($fixture === null) {
            throw new FoundationException("Unable to get client 'fixture'::'inspector' from the session's client pool.");
        }

        return $fixture;
    }

    protected function getTableOid($table_name)
    {
        return $this
            ->getInspector()
            ->getTableOid('inspector_test', $table_name)
            ;
    }

    public function setUp()
    {
        $this->getFixture()->createSchema();
    }

    public function tearDown()
    {
        $this->getFixture()->dropSchema();
    }

    public function testGetTableOid()
    {
        $this
            ->integer($this->getInspector()->getTableOid('inspector_test', 'no_pk'))
            ->variable($this->getInspector()->getTableOid('no schema', 'no table'))
            ->isNull()
            ->variable($this->getInspector()->getTableOid('inspector_test', 'no table'))
            ->isNull()
            ;
    }

    public function testGetTableFieldInformation()
    {
        $fields_info = $this->getInspector()->getTableFieldInformation($this->getTableOid('with_complex_pk'));
        $this
            ->object($fields_info)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->array($fields_info->slice('name'))
            ->isIdenticalTo(['with_complex_pk_id', 'another_id', 'created_at'])
            ->array($fields_info->slice('type'))
            ->isIdenticalTo(['int4', 'int4', 'timestamp'])
            ->array($fields_info->slice('comment'))
            ->isIdenticalTo(['Test comment', null, null])
            ;
    }

    public function testGetPrimaryKey()
    {
        $inspector = $this->getInspector();
        $this
            ->array($inspector->getPrimaryKey($this->getTableOid('no_pk')))
            ->isEmpty()
            ->array($inspector->getPrimaryKey($this->getTableOid('with_simple_pk')))
            ->isIdenticalTo(['with_simple_pk_id'])
            ->array($inspector->getPrimaryKey($this->getTableOid('with_complex_pk')))
            ->isIdenticalTo(['with_complex_pk_id', 'another_id'])
            ;
    }

    public function testGetSchemaOid()
    {
        $this
            ->integer($this->getInspector()->getSchemaOid('inspector_test'))
            ->variable($this->getInspector()->getSchemaOid('whatever'))
            ->isNull()
            ;
    }

    public function testGetSchemaRelations()
    {
        $tables_info = $this
            ->getInspector()
            ->getSchemaRelations($this
            ->getInspector()
            ->getSchemaOid('inspector_test')
        );

        $this
            ->object($tables_info)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->array($tables_info->slice('name'))
            ->isIdenticalTo(['no_pk', 'with_complex_pk', 'with_simple_pk'])
            ->boolean($this->getInspector()->getSchemaRelations(null)->isEmpty())
            ->isTrue()
            ;
    }

    public function testGetTableComment()
    {
        $this
            ->variable($this->getInspector()->getTableComment($this->getTableOid('with_simple_pk')))
            ->isNull()
            ->string($this->getInspector()->getTableComment($this->getTableOid('no_pk')))
            ->isEqualTo('This table has no primary key')
            ;
    }
}
