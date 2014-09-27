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

class PgBoolean extends Atoum
{
    public function testFromPg()
    {
        $this
            ->boolean($this->newTestedInstance()->fromPg('t', 'bool'))
            ->isTrue()
            ->boolean($this->newTestedInstance()->fromPg('f', 'bool'))
            ->isFalse()
            ->exception(function() { $this->newTestedInstance()->fromPg('whatever', 'bool'); })
            ->isInstanceOf('\PommProject\Foundation\Exception\ConverterException')
            ->message->contains('Unknown boolean data')
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(true, 'bool'))
            ->isEqualTo("bool 'true'")
            ->string($this->newTestedInstance()->toPg(false, 'bool'))
            ->isEqualTo("bool 'false'")
            ->string($this->newTestedInstance()->toPg(null, 'bool'))
            ->isEqualTo("NULL::bool")
            ;
    }
}
