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

use Atoum;

class PgTimestamp extends Atoum
{
    public function testFromPg()
    {
        $this
            ->datetime($this->newTestedInstance()->fromPg('2014-09-27 18:51:35.678406+00', 'timestamptz'))
            ->hasDateAndTime(2014, 9, 27, 18, 51, 35.678406)
            ->variable($this->newTestedInstance()->fromPg('NULL', 'timestamptz'))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(new \DateTime('2014-09-27 18:51:35.678406+00'), 'timestamptz'))
            ->isEqualTo("timestamptz '2014-09-27 18:51:35.678406+00:00'")
            ->string($this->newTestedInstance()->toPg(null, 'timestamptz'))
            ->isEqualTo("NULL::timestamptz")
            ;
    }
}
