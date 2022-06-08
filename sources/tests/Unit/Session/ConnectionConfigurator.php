<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Session;

class ConnectionConfigurator extends \Atoum
{
    public function testBadDsn($dsn)
    {
        $this
            ->exception(function () use ($dsn) {
                $this->newTestedInstance($dsn);
            })
            ->isInstanceOf(\PommProject\Foundation\Exception\ConnectionException::class);
    }

    protected function testBadDsnDataProvider()
    {
        return [
            'azertyuiop',
            'abcde://user:pass/host:1234/dbname',
            'pgsql://toto',
            'pgsql://toto:p4ssW0rD',
        ];
    }

    public function testGoodDsn($dsn, $connectionString)
    {
        $configurator = $this->newTestedInstance($dsn);
        $this->object($configurator)
            ->string($configurator->getConnectionString())
            ->isEqualTo($connectionString);
    }

    protected function testGoodDsnDataProvider()
    {
        return [
            [
                'pgsql://user:p4ssW0rD@a_host:5432/dbname',
                'user=user dbname=dbname host=a_host port=5432 password=p4ssW0rD',
            ],
            [
                'pgsql://user:p4ssW0rD@a_host:postgres/dbname',
                'user=user dbname=dbname host=a_host port=postgres password=p4ssW0rD',
            ],
            [
                'pgsql://user:p4ssW0rD@a_host/dbname',
                'user=user dbname=dbname host=a_host password=p4ssW0rD',
            ],
            [
                'pgsql://user:@a_host/dbname',
                'user=user dbname=dbname host=a_host',
            ],
            [
                'pgsql://user@a_host/dbname',
                'user=user dbname=dbname host=a_host',
            ],
            [
                'pgsql://user/dbname',
                'user=user dbname=dbname',
            ],
            [
                'pgsql://user:p4ssW0rD@172.18.210.109:5432/dbname',
                'user=user dbname=dbname host=172.18.210.109 port=5432 password=p4ssW0rD',
            ],
            [
                'pgsql://user:p4ssW0rD@172.18.210.109/dbname',
                'user=user dbname=dbname host=172.18.210.109 password=p4ssW0rD',
            ],
            [
                'pgsql://user:p4ssW0rD@!/var/run/pgsql!:5432/dbname',
                'user=user dbname=dbname host=/var/run/pgsql port=5432 password=p4ssW0rD',
            ],
            [
                'pgsql://user:p4ssW0rD@!/var/run/pgsql!/dbname',
                'user=user dbname=dbname host=/var/run/pgsql password=p4ssW0rD',
            ],
        ];
    }

    public function testGetConfiguration()
    {
        $configurator = $this->newTestedInstance('pgsql://user/dbname');

        $this->array($configurator->getConfiguration())
            ->isEmpty();
    }

    public function testAddConfiguration()
    {
        $configurator = $this->newTestedInstance('pgsql://user/dbname');
        $configuration = ['encoding' => 'utf-8'];

        $this->object($configurator->addConfiguration($configuration))
            ->isInstanceOf($this->getTestedClassName());

        $this->array($configurator->getConfiguration())
            ->isIdenticalTo($configuration);
    }

    public function testSet()
    {
        $configurator = $this->newTestedInstance('pgsql://user/dbname');
        $configuration = ['encoding' => 'utf-8'];

        $this->object($configurator->set('encoding', 'utf-8'))
            ->isInstanceOf($this->getTestedClassName());

        $this->array($configurator->getConfiguration())
            ->isIdenticalTo($configuration);
    }
}
