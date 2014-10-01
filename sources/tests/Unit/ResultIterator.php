<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class ResultIterator extends BaseConverter
{
    protected $session;

    protected function getPikaSql()
    {
        return <<<SQL
select
    p.id,
    p.pika
from
  (values
    (1::int4, 'a'::text),
    (2, 'b'),
    (3, 'c'),
    (4, 'd')
  ) p (id, pika)
SQL;
    }

    protected function getResultResource($sql, array $params = [])
    {
        return $this->getSession()->getConnection()->sendQueryWithParameters($sql, $params);
    }

    public function testConstructor()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource("select true::boolean"),
            $this->getSession()
        );

        $this
            ->object($iterator)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->isInstanceOf('\Countable')
            ->isInstanceOf('\Iterator')
            ;
    }

    public function testGet()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        $this
            ->array($iterator->get(0))
            ->isIdenticalTo(['id' => 1, 'pika' => 'a'])
            ->array($iterator->get(2))
            ->isIdenticalTo(['id' => 3, 'pika' => 'c'])
            ->array($iterator->get(1))
            ->isIdenticalTo(['id' => 2, 'pika' => 'b'])
            ->exception(function() use ($iterator) { return $iterator->get(5); })
            ->isInstanceOf('\OutOfBoundsException')
            ->message->contains('Cannot jump to non existing row')
            ;
    }

    public function testGetWithArray()
    {
        $sql = "select array[1, 2, 3, null]::int4[] as array_one, array[null, null]::int4[] as array_two";

        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        $this->integer($iterator->count())
            ->isEqualTo(1)
            ->array($iterator->current()['array_one'])
            ->isIdenticalTo([1, 2, 3, null])
            ->array($iterator->current()['array_two'])
            ->isIdenticalTo([null, null])
            ;
    }

    public function testGetWithNoType()
    {
        $sql = 'select null as one, array[null, null] as two';

        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        $this
            ->integer($iterator->count())
            ->isEqualTo(1)
            ->variable($iterator->current()['one'])
            ->isNull()
            ->array($iterator->current()['two'])
            ->isIdenticalTo([null, null])
            ;
    }

    public function testHasAndCount()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        $this
            ->integer($iterator->count())
            ->isEqualTo(4)
            ->boolean($iterator->has(1))
            ->isTrue()
            ->boolean($iterator->has(4))
            ->isFalse()
            ;
    }

    public function testIterator()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        foreach($iterator as $index => $element) {
            if ($index === 0) {
                $this
                    ->boolean($iterator->isFirst())
                    ->isTrue();
            } elseif ($index === $iterator->count() - 1) {
                $this
                    ->boolean($iterator->isLast())
                    ->isTrue();
            } else {
                $this
                    ->boolean($iterator->isFirst())
                    ->isFalse()
                    ->boolean($iterator->isLast())
                    ->isFalse()
                    ;
            }
            $this
                ->integer($element['id'])
                ->isEqualTo($index + 1)
                ;
        }
    }

    public function testSlice()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql),
            $this->getSession()
        );

        $this
            ->array($iterator->slice('pika'))
            ->isIdenticalTo(['a', 'b', 'c', 'd'])
            ->array($iterator->slice('id'))
            ->isIdenticalTo([1, 2, 3, 4])
            ->exception(function() use ($iterator) { return $iterator->slice('no_such_key'); })
            ->isInstanceOf('\InvalidArgumentException')
            ->message->contains('Cound not find field')
            ;
    }
}
