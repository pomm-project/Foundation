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

use PommProject\Foundation\Converter\Type\BaseRange;
use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Converter\Type\TsRange;

class PgTsRange extends BaseConverter
{
    protected function fromPgDataProvider()
    {
        return [
            [
                [
                    'assert'               => 'Test with infinity and null',
                    'text_range'           => '(infinity,)',
                    'expected_start_limit' => BaseRange::INFINITY_MAX,
                    'expected_end_limit'   => null,
                    'expected_start_incl'  => false,
                    'expected_end_incl'    => false,
                ],
            ],
            [
                [
                    'assert'               => 'Test with null and null',
                    'text_range'           => '[,]',
                    'expected_start_limit' => null,
                    'expected_end_limit'   => null,
                    'expected_start_incl'  => true,
                    'expected_end_incl'    => true,
                ],
            ],
            [
                [
                    'assert'               => 'Test with null and minus infinity',
                    'text_range'           => '(, -infinity)',
                    'expected_start_limit' => null,
                    'expected_end_limit'   => BaseRange::INFINITY_MIN,
                    'expected_start_incl'  => false,
                    'expected_end_incl'    => false,
                ],
            ],
            [
                [
                    'assert'               => 'Test with infinities',
                    'text_range'           => '(-infinity, infinity)',
                    'expected_start_limit' => BaseRange::INFINITY_MIN,
                    'expected_end_limit'   => BaseRange::INFINITY_MAX,
                    'expected_start_incl'  => false,
                    'expected_end_incl'    => false,
                ],
            ],
            [
                [
                    'assert'               => 'Test with empty',
                    'text_range'           => 'empty',
                    'expected_start_limit' => BaseRange::EMPTY_RANGE,
                    'expected_end_limit'   => BaseRange::EMPTY_RANGE,
                    'expected_start_incl'  => null,
                    'expected_end_incl'    => null,
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @dataProvider fromPgDataProvider
     */
    public function testExtraFromPg($data)
    {
        $session = $this->buildSession();
        $this
            ->assert($data['assert'])
            ->given($instance = $this->newTestedInstance()->fromPg($data['text_range'], 'tstzrange', $session))
            ->object($instance)
            ->isInstanceOf(\PommProject\Foundation\Converter\Type\TsRange::class)
            ->variable($instance->start_limit)
            ->isEqualTo($data['expected_start_limit'])
            ->variable($instance->end_limit)
            ->isEqualTo($data['expected_end_limit'])
            ->variable($instance->start_incl)
            ->isEqualTo($data['expected_start_incl'])
            ->variable($instance->end_incl)
            ->isEqualTo($data['expected_end_incl'])
        ;
    }

    public function testFromPg()
    {
        $session = $this->buildSession();
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $text_range_without_double_quote = '[2014-08-15 15:29:24.395639+00,2014-10-15 15:29:24.395639+00)';
        $this
            ->object($this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session))
            ->isInstanceOf(\PommProject\Foundation\Converter\Type\TsRange::class)
            ->variable($this->newTestedInstance()->fromPg(null, 'whatever', $session))
            ->isNull()
            ->variable($this->newTestedInstance()->fromPg('', 'whatever', $session))
            ->isNull()
            ;
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session);
        $this
            ->object($range->start_limit)
            ->isInstanceOf(\DateTime::class)
            ;
        $range_without_double_quote = $this->newTestedInstance()->fromPg($text_range_without_double_quote, 'tstzrange', $session);
        $this
            ->object($range_without_double_quote->start_limit)
            ->isInstanceOf(\DateTime::class)
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

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession();
        $text_range = '["2014-08-15 15:29:24.395639+00","2014-10-15 15:29:24.395639+00")';
        $range = $this->newTestedInstance()->fromPg($text_range, 'tstzrange', $session);

        $this
            ->string($this->newTestedInstance()->toPgStandardFormat($range, 'tstzrange', $session))
            ->isEqualTo('[""2014-08-15 15:29:24.395639+00"",""2014-10-15 15:29:24.395639+00"")')
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'mytsrange', $session))
            ->isNull()
            ;
        if ($this->isPgVersionAtLeast('9.2', $session)) {
            $this
                ->object($this->sendToPostgres($range, 'tsrange', $session))
                ->isInstanceOf(\PommProject\Foundation\Converter\Type\TsRange::class)
                ;
        } else {
            $this->skip('Skipping some PgTsRange tests because Pg version < 9.2.');
        }
    }
}
