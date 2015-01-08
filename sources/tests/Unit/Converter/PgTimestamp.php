<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgTimestamp extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $this
            ->datetime($this->newTestedInstance()->fromPg('2014-09-27 18:51:35.678406+00', 'timestamptz', $session))
            ->hasDateAndTime(2014, 9, 27, 18, 51, 35.678406)
            ->variable($this->newTestedInstance()->fromPg(null, 'timestamptz', $session))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toPg(new \DateTime('2014-09-27 18:51:35.678406+00'), 'timestamptz', $session))
            ->isEqualTo("timestamptz '2014-09-27 18:51:35.678406+00:00'")
            ->string($this->newTestedInstance()->toPg(null, 'timestamptz', $session))
            ->isEqualTo("NULL::timestamptz")
            ;
    }

    public function testToCsv()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toCsv(new \DateTime('2014-09-27 18:51:35.678406+00'), 'timestamptz', $session))
            ->isEqualTo('"2014-09-27 18:51:35.678406+00:00"')
            ->variable($this->newTestedInstance()->toCsv(null, 'timestamptz', $session))
            ->isNull()
            ;
    }
}
