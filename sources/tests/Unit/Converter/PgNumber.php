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


use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgNumber extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $this
            ->variable($this->newTestedInstance()->fromPg(null, 'int4', $session))
            ->isNull()
            ->integer($this->newTestedInstance()->fromPg('0', 'int4', $session))
            ->isEqualTo(0)
            ->integer($this->newTestedInstance()->fromPg('2015', 'int4', $session))
            ->isEqualTo(2015)
            ->float($this->newTestedInstance()->fromPg('3.141596', 'float4', $session))
            ->isEqualTo(3.141596)
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toPg(2014, 'int4', $session))
            ->isEqualTo("int4 '2014'")
            ->string($this->newTestedInstance()->toPg(1.6180339887499, 'float8', $session))
            ->isEqualTo("float8 '1.6180339887499'")
            ->string($this->newTestedInstance()->toPg(null, 'int4', $session))
            ->isEqualTo("NULL::int4")
            ->string($this->newTestedInstance()->toPg(0, 'int4', $session))
            ->isEqualTo("int4 '0'")
        ;
    }

    public function testToCsv()
    {
        $session = $this->buildSession();
        $this
            ->string($this->newTestedInstance()->toCsv(2014, 'int4', $session))
            ->isEqualTo("2014")
            ->string($this->newTestedInstance()->toCsv(1.6180339887499, 'float8', $session))
            ->isEqualTo("1.6180339887499")
            ->variable($this->newTestedInstance()->toCsv(null, 'int4', $session))
            ->isNull()
            ;
    }
}
