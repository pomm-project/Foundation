<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use Cake\Chronos\Chronos;
use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgTimestampChronos extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession('pomm_db2');
        $this
            ->datetime($this->newTestedInstance()->fromPg('2014-09-27 18:51:35.678406+00', 'timestamptz', $session))
            ->hasDateAndTime(2014, 9, 27, 18, 51, 35.678406)
            ->variable($this->newTestedInstance()->fromPg(null, 'timestamptz', $session))
            ->isNull();
    }

    public function testToPg()
    {
        $session = $this->buildSession('pomm_db2');
        $this
            ->string($this->newTestedInstance()
                ->toPg(new Chronos('2014-09-27 18:51:35.678406+00'), 'timestamptz', $session))
            ->isEqualTo("timestamptz '2014-09-27 18:51:35.678406+00:00'")
            ->string($this->newTestedInstance()->toPg(null, 'timestamptz', $session))
            ->isEqualTo("NULL::timestamptz");
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession('pomm_db2');
        $date_time = new Chronos('2014-09-27 18:51:35.678406+00');
        $this
            ->string($this->newTestedInstance()->toPgStandardFormat($date_time, 'timestamptz', $session))
            ->isEqualTo('2014-09-27 18:51:35.678406+00:00')
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'timestamptz', $session))
            ->isNull()
            ->object($this->sendToPostgres($date_time, 'timestamptz', $session))
            ->isInstanceof('Cake\Chronos\ChronosInterface')
            ->isEqualTo($date_time);
    }
}
