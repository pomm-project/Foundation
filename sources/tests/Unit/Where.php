<?php
/*
 * This file is part of the PommProject's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Where as PommWhere;
use Atoum;

class Where extends Atoum
{
    public function testCreate()
    {
        $this
            ->object(PommWhere::create())
            ->isInstanceOf(\PommProject\Foundation\Where::class)
            ->object(PommWhere::create('a = pika($*, $*)', [1, 2]))
            ->isInstanceOf(\PommProject\Foundation\Where::class)
            ;
    }

    public function testCreateWhereIn()
    {
        $where1 = PommWhere::createWhereIn('b', [1, 2, 3, 4]);
        $where2 = PommWhere::createWhereIn('(a, b)', [[1, 2], [3, 4]]);
        $this
            ->object($where1)
            ->isInstanceOf(\PommProject\Foundation\Where::class)
            ->string($where1->__toString())
            ->isEqualTo('b IN ($*, $*, $*, $*)')
            ->string($where2->__toString())
            ->isEqualTo('(a, b) IN (($*, $*), ($*, $*))')
            ;
    }

    public function testCreateWhereNotIn()
    {
        $where = PommWhere::createWhereNotIn('(a, b)', [[1, 2], [3, 4]]);
        $this
            ->object($where)
            ->isInstanceOf(\PommProject\Foundation\Where::class)
            ->string($where->__toString())
            ->isEqualTo('(a, b) NOT IN (($*, $*), ($*, $*))')
            ;
    }

    public function testIsEmpty()
    {
        $where = $this->newTestedInstance();
        $this
            ->boolean($where->isEmpty())
            ->isTrue()
            ->boolean($where->andWhere('a')->isEmpty())
            ->isFalse()
            ;
    }

    public function testAndWhere()
    {
        $where = $this->newTestedInstance('a', [1]);
        $this
            ->string($where->andWhere($this->newTestedInstance())->__toString())
            ->isEqualTo('a')
            ->string($where->andWhere($this->newTestedInstance('b'))->__toString())
            ->isEqualTo('(a AND b)')
            ->string($where->andWhere($this->newTestedInstance('c', [2, 3]))->__toString())
            ->isEqualTo('(a AND b AND c)')
            ->array($where->getValues())
            ->isIdenticalTo([1, 2, 3])
            ;
    }

    public function testOrWhere()
    {
        $where = $this->newTestedInstance('a', [1]);
        $this
            ->string($where->orWhere($this->newTestedInstance())->__toString())
            ->isEqualTo('a')
            ->string($where->orWhere($this->newTestedInstance('b'))->__toString())
            ->isEqualTo('(a OR b)')
            ->string($where->orWhere($this->newTestedInstance('c', [2, 3]))->__toString())
            ->isEqualTo('(a OR b OR c)')
            ->array($where->getValues())
            ->isIdenticalTo([1, 2, 3])
            ;
    }

    public function testAndOrWhere()
    {
        $where = $this->newTestedInstance('a', [1]);
        $where
            ->andWhere('b')
            ->orWhere('c', [2, 3])
            ->orWhere('d', [4])
            ->andWhere('e')
            ;
        $this
            ->string($where->__toString())
            ->isEqualTo('(((a AND b) OR c OR d) AND e)')
            ->array($where->getValues())
            ->isIdenticalTo([1, 2, 3, 4])
            ;
    }
}
