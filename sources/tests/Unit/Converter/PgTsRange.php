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
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $this
            ->object($this->newTestedInstance()->fromPg($text_range, 'tstzrange', $this->getSession()))
            ->isInstanceOf('\PommProject\Foundation\Converter\Type\TsRange')
            ->variable($this->newTestedInstance()->fromPg(null, 'point', $this->getSession()))
            ->isNull()
            ;
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $this->getSession());
        $this
            ->object($range->start_limit)
            ->isInstanceOf('\DateTime')
            ;
    }

    public function testToPg()
    {
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $this->getSession());

        $this
            ->string($this->newTestedInstance()->toPg($range, 'tstzrange', $this->getSession()))
            ->isEqualTo(sprintf("tstzrange('%s')", $text_range))
            ->string($this->newTestedInstance()->toPg(null, 'mytsrange', $this->getSession()))
            ->isEqualTo('NULL::mytsrange')
            ;
    }
}
