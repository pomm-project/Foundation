<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgArray extends BaseConverter
{
    public function testFromPg()
    {
        $converter = $this->newTestedInstance();

        $this
            ->variable($converter->fromPg(null, 'int4', $this->getSession()))
            ->isNull()
            ->array($converter->fromPg('{}', 'int4', $this->getSession()))
            ->isIdenticalTo([])
            ->array($converter->fromPg('{NULL}', 'int4', $this->getSession()))
            ->isIdenticalTo([null])
            ->array($converter->fromPg('{1,2,3,NULL}', 'int4', $this->getSession()))
            ->isIdenticalTo([1, 2, 3, null])
            ->array($converter->fromPg('{1.634,2.00001,3.99999,NULL}', 'float4', $this->getSession()))
            ->isIdenticalTo([1.634, 2.00001, 3.99999, null])
            ->array($converter->fromPg('{"ab a",aba,"a b a",NULL}', 'varchar', $this->getSession()))
            ->isIdenticalTo(['ab a', 'aba', 'a b a', null])
            ->array($converter->fromPg('{t,t,f,NULL}', 'bool', $this->getSession()))
            ->isIdenticalTo([true, true, false, null])
            ->array($converter->fromPg(
                '{"2014-09-29 18:24:54.591767","2014-07-29 14:50:01","2012-12-14 04:17:09.063948"}',
                'timestamp',
                $this->getSession()
            ))
            ->isEqualTo([
                new \DateTime('2014-09-29 18:24:54.591767'),
                new \DateTime('2014-07-29 14:50:01'),
                new \DateTime('2012-12-14 04:17:09.063948'),
            ])
            ;
    }

    public function testToPg()
    {
        $converter = $this->newTestedInstance();
        $this
            ->string($converter->toPg(null, 'int4', $this->getSession()))
            ->isEqualTo('NULL::int4[]')
            ->string($converter->toPg([null], 'int4', $this->getSession()))
            ->isEqualTo('ARRAY[NULL::int4]::int4[]')
            ->string($converter->toPg([1, 2, 3, null], 'int4', $this->getSession()))
            ->isEqualTo("ARRAY[int4 '1',int4 '2',int4 '3',NULL::int4]::int4[]")
            ->string($converter->toPg([1.634, 2.000, 3.99999, null], 'float4', $this->getSession()))
            ->isEqualTo("ARRAY[float4 '1.634',float4 '2',float4 '3.99999',NULL::float4]::float4[]")
            ->string($converter->toPg(['ab a', 'aba', 'a b a', null], 'varchar', $this->getSession()))
            ->isEqualTo("ARRAY[varchar 'ab a',varchar 'aba',varchar 'a b a',NULL::varchar]::varchar[]")
            ->string($converter->toPg([true, true, false, null], 'bool', $this->getSession()))
            ->isEqualTo("ARRAY[bool 'true',bool 'true',bool 'false',NULL::bool]::bool[]")
            ->string(
                $converter->toPg(
                    [
                        new \DateTime('2014-09-29 18:24:54.591767'),
                        new \DateTime('2014-07-29 14:50:01'),
                        new \DateTime('2012-12-14 04:17:09.063948'),
                    ],
                    'timestamp',
                    $this->getSession()
                )
            )
        ;
    }
}
