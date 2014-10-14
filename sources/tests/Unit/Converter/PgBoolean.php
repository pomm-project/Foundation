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

class PgBoolean extends BaseConverter
{
    public function testFromPg()
    {
        $this
            ->boolean($this->newTestedInstance()->fromPg('t', 'bool', $this->getSession()))
            ->isTrue()
            ->boolean($this->newTestedInstance()->fromPg('f', 'bool', $this->getSession()))
            ->isFalse()
            ->exception(function() { $this->newTestedInstance()->fromPg('whatever', 'bool', $this->getSession()); })
            ->isInstanceOf('\PommProject\Foundation\Exception\ConverterException')
            ->message->contains('Unknown bool data')
            ->variable($this->newTestedInstance()->fromPg(null, 'bool', $this->getSession()))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(true, 'bool', $this->getSession()))
            ->isEqualTo("bool 'true'")
            ->string($this->newTestedInstance()->toPg(false, 'bool', $this->getSession()))
            ->isEqualTo("bool 'false'")
            ->string($this->newTestedInstance()->toPg(null, 'bool', $this->getSession()))
            ->isEqualTo("NULL::bool")
            ;
    }
}
