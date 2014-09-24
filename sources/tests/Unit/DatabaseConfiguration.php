<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Test\Unit\Foundation;

use Atoum;

class DatabaseConfiguration extends Atoum
{
    protected function getDatabase(array $extra_options = [])
    {
        return $this->newTestedInstance(array_merge([
            'dsn'  =>  'pgsql://user:pass@host:port/db_name',
            'name' => 'db_name',
        ], $extra_options));
    }

    public function testGoodConstructor()
    {
        $database = $this->getDatabase();
        $this
            ->string($database->getParameterHolder()['dsn'])
            ->isEqualTo('pgsql://user:pass@host:port/db_name')
            ->string($database->getParameterHolder()['name'])
            ->isEqualTo('db_name')
            ;
    }

    public function testGetParameterHolder()
    {
        $database = $this->getDatabase(['an_option' => 'whatever']);
        $this
            ->object($database->getParameterHolder())
            ->isInstanceOf('\PommProject\Foundation\ParameterHolder')
            ->string($database->getParameterHolder()->getParameter('dsn'))
            ->isEqualTo('pgsql://user:pass@host:port/db_name')
            ->string($database->getParameterHolder()->getParameter('an_option'))
            ->isEqualTo('whatever')
            ;
    }

    public function testGetConverterHolder()
    {
        $database = $this->getDatabase();
        $this
            ->object($database->getConverterHolder())
            ->isInstanceOf('\PommProject\Foundation\Converter\ConverterHolder')
            ->boolean($database->getConverterHolder()->hasType('int4'))
            ->isTrue()
            ;
    }
}
