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

class PgFloat extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $this
            ->variable($this->newTestedInstance()->fromPg(null, 'int4', $session))
            ->isNull()
            ->float($this->newTestedInstance()->fromPg('0', 'int4', $session))
            ->isEqualTo(0)
            ->float($this->newTestedInstance()->fromPg('2015', 'int4', $session))
            ->isEqualTo(2015)
            ->float($this->newTestedInstance()->fromPg('3.141596', 'float4', $session))
            ->isEqualTo(3.141596)
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toPg(2014, 'float8', $session))
            ->isEqualTo("float8 '2014'")
            ->string($this->newTestedInstance()->toPg(1.6180339887499, 'float8', $session))
            ->isEqualTo("float8 '1.6180339887499'")
            ->string($this->newTestedInstance()->toPg(null, 'float8', $session))
            ->isEqualTo("NULL::float8")
            ->string($this->newTestedInstance()->toPg(0, 'float8', $session))
            ->isEqualTo("float8 '0'")
        ;
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toPgStandardFormat(2014, 'float8', $session))
            ->isEqualTo("2014")
            ->string($this->newTestedInstance()->toPgStandardFormat(1.6180339887499, 'float8', $session))
            ->isEqualTo("1.6180339887499")
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'float8', $session))
            ->isNull()
            ;
    }
}
