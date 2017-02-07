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

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgDateRangeChronos extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession('pomm_db2');
        $text_range = '["2014-08-15","2014-10-15")';
        $text_range_without_double_quote = '[2014-08-15,2014-10-15)';
        $this
            ->object($this->newTestedInstance()->fromPg($text_range, 'daterange', $session))
            ->isInstanceOf('\PommProject\Foundation\Converter\Type\DateRangeChronos')
            ->variable($this->newTestedInstance()->fromPg(null, 'whatever', $session))
            ->isNull()
            ->variable($this->newTestedInstance()->fromPg('', 'whatever', $session))
            ->isNull();
        $range = $this->newTestedInstance()->fromPg($text_range, 'daterange', $session);
        $this
            ->object($range->start_limit)
            ->isInstanceOf('Cake\Chronos\Date');
        $range_without_double_quote = $this->newTestedInstance()
            ->fromPg($text_range_without_double_quote, 'daterange', $session);
        $this
            ->object($range_without_double_quote->start_limit)
            ->isInstanceOf('Cake\Chronos\Date');
    }

    public function testToPg()
    {
        $session = $this->buildSession('pomm_db2');
        $text_range = '["2014-08-15","2014-10-15")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'daterange', $session);

        $this
            ->string($this->newTestedInstance()->toPg($range, 'daterange', $session))
            ->isEqualTo(sprintf("daterange('%s')", $text_range))
            ->string($this->newTestedInstance()->toPg(null, 'mydaterange', $session))
            ->isEqualTo('NULL::mydaterange');
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession('pomm_db2');
        $text_range = '["2014-08-15","2014-10-15")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'daterange', $session);

        $this
            ->string($this->newTestedInstance()->toPgStandardFormat($range, 'daterange', $session))
            ->isEqualTo('[""2014-08-15"",""2014-10-15"")')
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'mydaterange', $session))
            ->isNull();
        if ($this->isPgVersionAtLeast('9.2', $session)) {
            $this
                ->object($this->sendToPostgres($range, 'daterange', $session))
                ->isInstanceOf('\PommProject\Foundation\Converter\Type\DateRangeChronos');
        } else {
            $this->skip('Skipping some PgDateRangeChronos tests because Pg version < 9.2.');
        }
    }
}
