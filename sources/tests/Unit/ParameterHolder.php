<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Test\Unit\Foundation;

use Atoum;

class ParameterHolder extends Atoum
{
    public function testConstructorEmpty()
    {
        $parameter_holder = $this->newTestedInstance();
        $this
            ->array($parameter_holder->getIterator()->getArrayCopy())
            ->isEmpty()
            ;
    }

    public function testConstructorWithParameter()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->array($parameter_holder->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'one'])
            ;
    }

    public function testSetParameter()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->array($parameter_holder->setParameter('chu', 'two')->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'one', 'chu' => 'two'])
            ->array($parameter_holder->setParameter('pika', 'three')->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'three', 'chu' => 'two'])
            ->array($parameter_holder->setParameter('plop', null)->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'three', 'chu' => 'two', 'plop' => null])
            ->array($parameter_holder->setParameter('pika', null)->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => null, 'chu' => 'two', 'plop' => null])
            ;
    }

    public function testHasParameter()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->boolean($parameter_holder->hasParameter('pika'))
            ->isTrue()
            ->boolean($parameter_holder->hasParameter('chu'))
            ->isFalse()
            ->boolean($parameter_holder->setParameter('chu', 'whatever')->hasParameter('chu'))
            ->isTrue()
            ->boolean($parameter_holder->setParameter('chu', null)->hasParameter('chu'))
            ->isTrue()
            ;
    }

    public function testGetParameter()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->string($parameter_holder->getParameter('pika'))
            ->isEqualTo('one')
            ->string($parameter_holder->getParameter('pika', 'two'))
            ->isEqualTo('one')
            ->string($parameter_holder->getParameter('chu', 'two'))
            ->isEqualTo('two')
            ->variable($parameter_holder->getParameter('chu'))
            ->isNull()
            ;
    }

    public function testMustHave()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->object($parameter_holder->mustHave('pika'))
            ->isInstanceOf(\PommProject\Foundation\ParameterHolder::class)
            ->exception(function () use ($parameter_holder) { $parameter_holder->mustHave('chu'); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('mandatory')
            ->object($parameter_holder->setParameter('chu', 'whatever')->mustHave('chu'))
            ->isInstanceOf(\PommProject\Foundation\ParameterHolder::class)
            ;
    }

    public function testSetDefaultValue()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->string(
                $parameter_holder->setDefaultValue('pika', 'two')->getParameter('pika'))
                ->isEqualTo('one')
                ->string($parameter_holder->setDefaultValue('chu', 'two')->getParameter('chu'))
                ->isEqualTo('two')
                ;
    }

    public function testMustBeOneOf()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one', 'chu' => 'two']);
        $this
            ->object($parameter_holder->mustBeOneOf('pika', [ 'one', 'two', 'tree']))
            ->isInstanceOf(\PommProject\Foundation\ParameterHolder::class)
            ->exception(function () use ($parameter_holder) { $parameter_holder->mustBeOneOf('pika', ['four', 'five']); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('must be one of')
            ->exception(function () use ($parameter_holder) { $parameter_holder->mustBeOneOf('chu', ['four', 'five']); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('must be one of')
            ;
    }

    public function testUnsetParameter()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one', 'chu' => 'two']);
        $this
            ->array($parameter_holder->unsetParameter('pika')->getIterator()->getArrayCopy())
            ->isIdenticalTo(['chu' => 'two'])
            ->array($parameter_holder->unsetParameter('chu')->getIterator()->getArrayCopy())
            ->isEmpty()
            ->array($parameter_holder->unsetParameter('chu')->getIterator()->getArrayCopy())
            ->isEmpty()
            ;
    }

    public function testArrayAccess()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->object($parameter_holder)
            ->isInstanceOf(\ArrayAccess::class)
            ->boolean(isset($parameter_holder['pika']))
            ->isTrue()
            ->string($parameter_holder['pika'])
            ->isEqualTo('one')
            ->boolean(isset($parameter_holder['chu']))
            ->isFalse()
            ;
        $parameter_holder['chu'] = 'two';
        $this
            ->boolean(isset($parameter_holder['chu']))
            ->isTrue()
            ->string($parameter_holder['chu'])
            ->isEqualTo('two')
            ;
        unset($parameter_holder['chu']);
        $this
            ->boolean(isset($parameter_holder['chu']))
            ->isFalse()
            ;
    }

    public function testCount()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->object($parameter_holder)
            ->isInstanceOf(\Countable::class)
            ->integer($parameter_holder->count())
            ->isEqualTo(1)
            ->integer($parameter_holder->setParameter('chu', 'two')->count())
            ->isEqualTo(2)
            ;
    }

    public function testGetIterator()
    {
        $parameter_holder = $this->newTestedInstance(['pika' => 'one']);
        $this
            ->object($parameter_holder)
            ->isInstanceOf(\IteratorAggregate::class)
            ->array($parameter_holder->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'one'])
            ->array($parameter_holder->setParameter('chu', 'two')->getIterator()->getArrayCopy())
            ->isIdenticalTo(['pika' => 'one', 'chu' => 'two'])
            ;
    }
}
