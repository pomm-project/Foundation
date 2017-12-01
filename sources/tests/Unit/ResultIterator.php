<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Tester\VanillaSessionAtoum;

class ResultIterator extends VanillaSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

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
        return $this->buildSession()->getConnection()->sendQueryWithParameters($sql, $params);
    }

    public function testConstructor()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource("select true::boolean")
        );

        $this
            ->object($iterator)
            ->isInstanceOf('\PommProject\Foundation\ResultIterator')
            ->isInstanceOf('\Countable')
            ->isInstanceOf('\PommProject\Foundation\ResultIteratorInterface')
            ;
    }

    public function testGet()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql)
        );

        $this
            ->array($iterator->get(0))
            ->isIdenticalTo(['id' => '1', 'pika' => 'a'])
            ->array($iterator->get(2))
            ->isIdenticalTo(['id' => '3', 'pika' => 'c'])
            ->array($iterator->get(1))
            ->isIdenticalTo(['id' => '2', 'pika' => 'b'])
            ->exception(function () use ($iterator) {
                return $iterator->get(5);
            })
            ->isInstanceOf('\OutOfBoundsException')
            ->message->contains('Cannot jump to non existing row')
            ;
    }

    public function testGetOnEmptyResult()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource('select true where false')
        );
        $this
            ->integer($iterator->count())
            ->isEqualTo(0)
            ->boolean($iterator->isEmpty())
            ->isTrue()
            ->variable($iterator->current())
            ->isNull()
            ->variable($iterator->isLast())
            ->isNull()
            ->variable($iterator->isFirst())
            ->isNull()
            ;
    }

    public function testHasAndCount()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql)
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
            $this->getResultResource($sql)
        );

        foreach ($iterator as $index => $element) {
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
                ->string($element['id'])
                ->isEqualTo($index + 1)
                ;
        }
    }

    public function testSlice()
    {
        $sql = $this->getPikaSql();
        $iterator = $this->newTestedInstance(
            $this->getResultResource($sql)
        );

        $this
            ->array($iterator->slice('pika'))
            ->isIdenticalTo(['a', 'b', 'c', 'd'])
            ->array($iterator->slice('id'))
            ->isIdenticalTo(['1', '2', '3', '4'])
            ->exception(function () use ($iterator) {
                return $iterator->slice('no_such_key');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->message->contains('Could not find field')
            ;
    }

    public function testExtract()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource($this->getPikaSql())
        );

        $this
            ->array($iterator->extract())
            ->isIdenticalTo(
                [
                    ['id' => '1', 'pika' => 'a'],
                    ['id' => '2', 'pika' => 'b'],
                    ['id' => '3', 'pika' => 'c'],
                    ['id' => '4', 'pika' => 'd'],
                ]
            );

        $iterator = $this->newTestedInstance(
            $this->getResultResource('select true where false')
        );

        $this
            ->array($iterator->extract())
            ->isEmpty()
            ;
    }

    public function testExtractGenerator()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource($this->getPikaSql())
        );

        $arrayTmp = [];

        foreach ($iterator->extractGenerator() as $val) {
            $arrayTmp[] = $val;
        }

        $this
            ->array($arrayTmp)
            ->isIdenticalTo(
                [
                    ['id' => '1', 'pika' => 'a'],
                    ['id' => '2', 'pika' => 'b'],
                    ['id' => '3', 'pika' => 'c'],
                    ['id' => '4', 'pika' => 'd'],
                ]
            );

        $iterator = $this->newTestedInstance(
            $this->getResultResource('select true where false')
        );

        $arrayTmp = [];

        foreach ($iterator->extractGenerator() as $val) {
            $arrayTmp[] = $val;
        }

        $this
            ->array($arrayTmp)
            ->isEmpty()
        ;
    }

    public function testJsonSerializable()
    {
        $iterator = $this->newTestedInstance(
            $this->getResultResource($this->getPikaSql())
        );

        $json = json_encode($iterator);
        $this
            ->string($json)
            ->isIdenticalTo(
                '[{"id":"1","pika":"a"},{"id":"2","pika":"b"},{"id":"3","pika":"c"},{"id":"4","pika":"d"}]'
            );
    }
}
