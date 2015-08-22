<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Converter\PgComposite as PommComposite;

class PgComposite extends BaseConverter
{
    protected function initializeSession(Session $session)
    {
        parent::initializeSession($session);

        $session
            ->getPoolerForType('converter')
            ->getConverterHolder()
            ->registerConverter('MyComposite', new PommComposite(['a' => 'int4', 'b' => 'varchar[]']), ['test_type'])
            ;
    }

    public function setUp()
    {
        $this
            ->buildSession()
            ->getConnection()
            ->executeAnonymousQuery('create type test_type as (a int4, b varchar[])')
            ;
    }

    public function tearDown()
    {
        $this
            ->buildSession()
            ->getConnection()
            ->executeAnonymousQuery('drop type test_type cascade')
            ;
    }

    public function testFromPg()
    {
        $converter = $this->newTestedInstance(['a' => 'int4', 'b' => 'varchar[]']);
        $session   = $this->buildSession();
        $string    = '(3,"{pika,chu}")';

        $this
            ->array($converter->fromPg($string, 'test_type', $session))
            ->isIdenticalTo(['a' => 3, 'b' => ['pika', 'chu']])
            ->variable($converter->fromPg(null, 'test_type', $session))
            ->isNull()
            ->array($converter->fromPg('(,{})', 'test_type', $session))
            ->isIdenticalTo(['a' => null, 'b' => []])
            ;
    }

    public function testToPg()
    {
        $converter = $this->newTestedInstance(['a' => 'int4', 'b' => 'varchar[]']);
        $session   = $this->buildSession();

        $this
            ->string($converter->toPg(['a' => 3, 'b' => ['pika', 'chu']], 'test_type', $session))
            ->isEqualTo("ROW(int4 '3',ARRAY[varchar 'pika',varchar 'chu']::varchar[])::test_type")
            ->string($converter->toPg(['a' => null, 'b' => []], 'test_type', $session))
            ->isEqualTo("ROW(NULL::int4,ARRAY[]::varchar[])::test_type")
            ->string($converter->toPg(null, 'test_type', $session))
            ->isEqualTo("NULL::test_type")
            ;
    }

    public function testToPgStandardFormat()
    {
        $converter = $this->newTestedInstance(['a' => 'int4', 'b' => 'varchar[]']);
        $session   = $this->buildSession();
        $data      = ['a' => 3, 'b' => ['pika', 'chu']];

        $this
            ->string($converter->toPgStandardFormat($data, 'test_type', $session))
            ->isEqualTo('(3,"{pika,chu}")')
            ->string($converter->toPgStandardFormat(['a' => null, 'b' => []], 'test_type', $session))
            ->isEqualTo('(,{})')
            ->variable($converter->toPgStandardFormat(null, 'test_type', $session))
            ->isNull()
            ->array($this->sendToPostgres($data, 'test_type', $session))
            ->isIdenticalTo($data)
            ;
    }
}
