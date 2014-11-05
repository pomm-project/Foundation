<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\QueryManager;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

class SimpleQueryManager extends FoundationSessionAtoum
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
}
