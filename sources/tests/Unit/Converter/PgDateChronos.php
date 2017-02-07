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

use Cake\Chronos\Date;

class PgDateChronos extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession('pomm_db2');
        $this
            ->datetime($this->newTestedInstance()->fromPg('2014-09-27 00:00:00+00', 'date', $session))
            ->hasDateAndTime(2014, 9, 27, 0, 0, 0)
            ->variable($this->newTestedInstance()->fromPg(null, 'date', $session))
            ->isNull();
    }

    public function testToPg()
    {
        $session = $this->buildSession('pomm_db2');
        $this
            ->string($this->newTestedInstance()
                ->toPg(new Date('2014-09-27'), 'date', $session))
            ->isEqualTo("date '2014-09-27'")
            ->string($this->newTestedInstance()->toPg(null, 'date', $session))
            ->isEqualTo("NULL::date");
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession('pomm_db2');
        $date_time = new Date('2014-09-27');
        $this
            ->string($this->newTestedInstance()->toPgStandardFormat($date_time, 'date', $session))
            ->isEqualTo('2014-09-27')
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'date', $session))
            ->isNull()
            ->object($this->sendToPostgres($date_time, 'date', $session))
            ->isInstanceof('Cake\Chronos\Date')
            ->isEqualTo($date_time);
    }
}
