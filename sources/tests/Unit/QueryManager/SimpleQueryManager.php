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

use PommProject\Foundation\Session;
use PommProject\Foundation\DatabaseConfiguration;
use Atoum;

class SimpleQueryManager extends Atoum
{
    protected $session;

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = new Session(new DatabaseConfiguration($GLOBALS['pomm_db1']));
        }

        return $this->session;
    }

    protected function getQueryManager()
    {
        return $this
            ->newTestedInstance()
            ->initialize($this->getSession())
            ;
    }

    public function testSimpleQuery()
    {
        $iterator = $this->getQueryManager()->query('select true as one');
        $this
            ->object($iterator)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->boolean($iterator->current()['one'])
            ->isTrue()
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
