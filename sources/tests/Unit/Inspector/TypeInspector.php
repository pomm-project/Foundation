<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Inspector;

use PommProject\Foundation\Where;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

class TypeInspector extends FoundationSessionAtoum
{
    use InspectorTestTrait;

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector('type');
    }

    public function testGetUserTypes()
    {
        $iterator = $this
            ->getInspector()
            ->getTypes(Where::create('typname = $*', ['circle']))
            ;
        $this
            ->object($iterator)
            ->isInstanceOf(ConvertedResultIterator::class)
            ->integer($iterator->count())
            ->isEqualTo(1)
            ;
        $info = $iterator->current();
        $this->array($info)
            ->hasKeys(['name', 'schema', 'oid', 'category', 'owner', 'description'])
            ->containsValues(['circle', 'pg_catalog', 'geometric'])
            ;
    }

    public function testGetTypesInSchema()
    {
        $iterator = $this
            ->getInspector()
            ->getTypesInSchema(
                'inspector_test',
                Where::create("t.typname ~* $*", ['someone|sponsor'])
            )
            ;
        $this
            ->assert('getTypesInSchema returns an iterator on results.')
                ->object($iterator)
                ->isInstanceOf(ConvertedResultIterator::class)
                ->integer($iterator->count())
                ->isEqualTo(4)
            ->assert('getTypesInSchema returns the correct types.')
                ->given($type = $iterator->get(0))
                    ->string($type['name'])
                    ->isEqualTo('someone')
                    ->string($type['category'])
                    ->isEqualTo('composite')
                ->given($type = $iterator->get(2))
                    ->string($type['name'])
                    ->isEqualTo('sponsor_rating')
                    ->string($type['category'])
                    ->isEqualTo('enumerated')
            ;
    }
}
