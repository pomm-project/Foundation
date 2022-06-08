<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Test\Fixture\DumbConverter;
use PommProject\Foundation\Session\Session;
use Atoum;

class ConverterHolder extends Atoum
{
    public function testRegisterConverter()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->array($converter_holder->registerConverter(
            'Dumb',
            new DumbConverter(),
            ['schema.type', 'public.dumb']
        )->getTypes())
        ->isIdenticalTo(['schema.type', 'public.dumb'])
        ->array($converter_holder->getConverterNames())
        ->isIdenticalTo(['Dumb'])
        ->object($converter_holder->getConverterForType('public.dumb'))
        ->isInstanceOf(\PommProject\Foundation\Test\Fixture\DumbConverter::class)
        ;
    }

    public function testHasConverterName()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->boolean($converter_holder->hasConverterName('Dumb'))
            ->isFalse()
            ->boolean($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['schema.type', 'public.dumb']
        )->hasConverterName('Dumb'))
        ->isTrue()
            ;
    }

    public function testGetConverter()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->variable($converter_holder->getConverter('Dumb'))
            ->isNull()
            ->object($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['schema.type', 'public.dumb']
        )->getConverter('Dumb'))
        ->isInstanceOf(\PommProject\Foundation\Test\Fixture\DumbConverter::class)
            ;
    }

    public function testGetConverterNames()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->array($converter_holder->getConverterNames())
            ->isEmpty()
            ->array($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['public.dumb']
            )->getConverterNames())
            ->isIdenticalTo(['Dumb'])
            ;
    }

    public function testAddTypeToConverter()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->array($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['public.dumb']
            )->addTypeToConverter('Dumb', 'schema.type')->getTypes())
            ->isIdenticalTo(['public.dumb', 'schema.type'])
            ->array($converter_holder->addTypeToConverter('Dumb', 'pika.chu')->getTypes())
            ->isIdenticalTo(['public.dumb', 'schema.type', 'pika.chu'])
            ->exception(
                function () use ($converter_holder) {
                    $converter_holder->addTypeToConverter('No', 'pika.chu');
                })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('No such converter')
            ;
    }

    public function testGetConverterForType()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->object($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['schema.type', 'public.dumb']
            )->getConverterForType('schema.type'))
            ->isInstanceOf(\PommProject\Foundation\Test\Fixture\DumbConverter::class)
            ->exception(function () use ($converter_holder) { $converter_holder->getConverterForType('no.type'); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('No converters associated with type')
            ;
    }

    public function testHasType()
    {
        $converter_holder = $this->newTestedInstance();
        $this
            ->boolean($converter_holder->hasType('pika.chu'))
            ->isFalse()
            ->boolean($converter_holder->registerConverter(
                'Dumb',
                new DumbConverter(),
                ['pika.chu']
            )->hasType('pika.chu'))
            ->isTrue()
            ;
    }
}
