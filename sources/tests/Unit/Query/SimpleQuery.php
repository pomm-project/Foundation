<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Query;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Test\Fixture\QueryListener;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

class SimpleQuery extends FoundationSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

    protected function getQueryManager(Session $session)
    {
        $query_manager = $this->newTestedInstance();
        $session->registerClient($query_manager);

        return $query_manager;
    }

    public function testSimpleQuery()
    {
        $session = $this->buildSession();
        $iterator = $this->getQueryManager($session)->query('select true as one, null::int4 as two');
        $this
            ->object($iterator)
            ->isInstanceOf('\PommProject\Foundation\ConvertedResultIterator')
            ->boolean($iterator->current()['one'])
            ->isTrue()
            ->variable($iterator->current()['two'])
            ->isNull()
            ;
    }

    public function testParametrizedQuery()
    {
        $session = $this->buildSession();
        $sql = <<<SQL
select
  p.id, p.pika
from (values
    (1, 'one'),
    (2, 'two'),
    (3, 'three'),
    (4, 'four')
) p (id, pika)
where p.id = $* or p.pika = $*
SQL;
        $iterator = $this->getQueryManager($session)->query($sql, [2, 'three']);
        $this
            ->array($iterator->slice('id'))
            ->isIdenticalTo([2, 3])
            ;
    }

    public function testNotification()
    {
        $session = $this->buildSession();
        $listener = new QueryListener;
        $this
            ->getQueryManager($session)
            ->registerListener($listener)
            ->query('select generate_series(1, 99) as id')
            ;

        $this
            ->integer($listener->getCounter())
            ->isEqualTo(2)
            ->string($listener->getLastEventType())
            ->isEqualTo('post')
            ->integer($listener->getLastEventData()['result_count'])
            ->isEqualTo(99)
            ->string($listener->getLastEventType())
            ->isEqualTo('pre')
            ->string($listener->getLastEventData()['sql'])
            ->isEqualTo('select generate_series(1, 99) as id')
            ;
    }
}
