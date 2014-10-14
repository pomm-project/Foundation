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
        $this
            ->variable($this->newTestedInstance()->fromPg(null, 'int4', $this->getSession()))
            ->isNull()
            ->integer($this->newTestedInstance()->fromPg('2015', 'int4', $this->getSession()))
            ->isEqualTo(2015)
            ->float($this->newTestedInstance()->fromPg('3.141596', 'float4', $this->getSession()))
            ->isEqualTo(3.141596)
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(2014, 'int4', $this->getSession()))
            ->isEqualTo("int4 '2014'")
            ->string($this->newTestedInstance()->toPg(1.6180339887499, 'float8', $this->getSession()))
            ->isEqualTo("float8 '1.6180339887499'")
            ->string($this->newTestedInstance()->toPg(null, 'int4', $this->getSession()))
            ->isEqualTo("NULL::int4")
            ;
    }
}
