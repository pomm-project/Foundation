<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Session;

use Atoum;
use PommProject\Foundation\Session\Connection as PommConnection;

class Connection extends Atoum
{
    protected function getDsn()
    {
        $var = $GLOBALS['pomm_db1'];

        return $var['dsn'];
    }

    protected function getConnection($dsn)
    {
        return $this->newTestedInstance($dsn);
    }

    public function badDsnDataProvider()
    {
        return
            [
                'azertyuiop',
                'abcde://user:pass/host:1234/dbname',
                'pgsql://toto',
                'pgsql://toto:p4ssW0rD',
            ];
    }

    public function goodDsnDataProvider()
    {
        return
            [
                'pgsql://user:p4ssW0rD/a_host:5432/dbname',
                'pgsql://user:p4ssW0rD/a_host:postgres/dbname',
                'pgsql://user:p4ssW0rD/a_host/dbname',
                'pgsql://user/a_host/dbname',
                'pgsql://user/dbname',
                'pgsql://user:p4ssW0rD/172.18.210.109:5432/dbname',
                'pgsql://user:p4ssW0rD/172.18.210.109/dbname',
                'pgsql://user:p4ssW0rD/!/var/run/pgsql!:5432/dbname',
                'pgsql://user:p4ssW0rD/!/var/run/pgsql!/dbname',
            ];
    }

    /**
     * @dataProvider badDsnDataProvider
     */
    public function testBadConstructor($dsn)
    {
        $this
            ->exception(function() use ($dsn) { $this->getConnection($dsn); })
            ->isInstanceOf('\PommProject\Foundation\Exception\ConnectionException')
            ;
    }

    /**
     * @dataProvider goodDsnDataProvider
     */
    public function testGoodConstructor($dsn)
    {
        $connection = $this->getConnection($dsn);
        $this->object($connection)
            ->integer($connection->getConnectionStatus())
            ->isEqualTo(PommConnection::CONNECTION_STATUS_NONE)
            ;
    }

    public function testExecuteAnonymousQuery()
    {
        $connection = $this->getConnection($this->getDsn());
        $this
            ->object($connection->executeAnonymousQuery('select true'))
            ->isInstanceOf('\PommProject\Foundation\Session\ResultHandler')
            ->exception(function() use ($connection) {
                    $connection->executeAnonymousQuery('bad query');
                })
            ->isInstanceOf('\PommProject\Foundation\Exception\SqlException')
            ->array($connection->executeAnonymousQuery('select true; select false; select null'))
            ->hasSize(3)
            ->exception(function() use ($connection) {
                    $connection->executeAnonymousQuery('select true; bad query');
                })
            ->isInstanceOf('\PommProject\Foundation\Exception\SqlException')
            ;
    }
}
