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
use PommProject\Foundation\Converter\Type\TsRange;

class PgTsRange extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $this
            ->object($this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session))
            ->isInstanceOf('\PommProject\Foundation\Converter\Type\TsRange')
            ->variable($this->newTestedInstance()->fromPg(null, 'point', $session))
            ->isNull()
            ;
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session);
        $this
            ->object($range->start_limit)
            ->isInstanceOf('\DateTime')
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session);

        $this
            ->string($this->newTestedInstance()->toPg($range, 'tstzrange', $session))
            ->isEqualTo(sprintf("tstzrange('%s')", $text_range))
            ->string($this->newTestedInstance()->toPg(null, 'mytsrange', $session))
            ->isEqualTo('NULL::mytsrange')
            ;
    }

    public function testToCsv()
    {
        $session = $this->buildSession();
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session);

        $this
            ->string($this->newTestedInstance()->toCsv($range, 'tstzrange', $session))
            ->isEqualTo('[""2014-08-15 15:29:24.395639+00"",""2014-10-15 15:29:24.395639+00"")')
            ->variable($this->newTestedInstance()->toCsv(null, 'mytsrange', $session))
            ->isNull()
            ;
    }
}
