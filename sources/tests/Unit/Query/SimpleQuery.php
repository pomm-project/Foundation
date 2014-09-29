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

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class SimpleQuery extends BaseConverter
{
    protected function getQueryManager()
    {
        $query_manager = $this->newTestedInstance();
        $query_manager->initialize($this->getSession());

        return $query_manager;
    }

    public function testSimpleQuery()
    {
        $iterator = $this->getQueryManager()->query('select true as one, null::int4 as two');
        $this
            ->object($iterator)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->boolean($iterator->current()['one'])
            ->isTrue()
            ->variable($iterator->current()['two'])
            ->isNull()
            ;
    }

    public function testParametrizedQuery()
    {
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
        $iterator = $this->getQueryManager()->query($sql, [2, 'three']);
        $this
            ->array($iterator->slice('id'))
            ->isIdenticalTo([2, 3])
            ;
    }
}
