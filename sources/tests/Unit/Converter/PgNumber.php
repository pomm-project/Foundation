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

use Atoum;

class PgNumber extends Atoum
{
    public function testFromPg()
    {
        $this
            ->integer($this->newTestedInstance()->fromPg('2014', 'int4'))
            ->isEqualTo(2014)
            ->float($this->newTestedInstance()->fromPg('3.141596', 'float4'))
            ->isEqualTo(3.141596)
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(2014, 'int4'))
            ->isEqualTo("int4 '2014'")
            ->string($this->newTestedInstance()->toPg(1.6180339887499, 'float8'))
            ->isEqualTo("float8 '1.6180339887499'")
            ;
    }
}
