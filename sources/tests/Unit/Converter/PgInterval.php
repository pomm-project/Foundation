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

class PgInterval extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $this
            ->variable($this->newTestedInstance()->fromPg(null, 'interval', $session))
            ->isNull()
            ->dateInterval($this->newTestedInstance()->fromPg('P14346DT22H47M3.138892S', 'interval', $session))
            ->isEqualTo(new \DateInterval('P14346DT22H47M3S')) // <- truncated to second precision
            ->exception(function () use ($session) { $this->newTestedInstance()->fromPg('P-11D', 'interval', $session); })
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->message->contains('is not an ISO8601 interval representation')
        ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toPg(null, 'interval', $session))
            ->isEqualTo('NULL::interval')
            ->string($this->newTestedInstance()->toPg(new \DateInterval('P14346DT22H47M3S'), 'interval', $session))
            ->isEqualTo("interval '00 years 00 months 14346 days 22:47:03'")
            ->exception(function () use ($session) { $this->newTestedInstance()->toPg('foo', 'interval', $session); })
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->message->contains('First argument is not a \DateInterval instance')
            ;
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession();
        $this
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'interval', $session))
            ->isNull()
            ->string($this->newTestedInstance()->toPgStandardFormat(new \DateInterval('P14346DT22H47M3S'), 'interval', $session))
            ->isEqualTo('"00 years 00 months 14346 days 22:47:03"')
            ;
    }
}
