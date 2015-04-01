<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Session\SessionBuilder;
use PommProject\Foundation\Session\Session;
use Atoum;

class Pomm extends Atoum
{
    protected function getSessionBuilder(array $config = [])
    {
        return new SessionBuilder($config);
    }

    protected function getPomm(array $configuration = null)
    {
        if ($configuration === null) {
            $configuration =
                [
                    "db_one"   => ["dsn" => "pgsql://user:pass@host:port/db_name"],
                    "db_two"   => [
                        "dsn" => "pgsql://user:pass@host:port/db_name",
                        "class:session_builder" => "PommProject\\Foundation\\Test\\Fixture\\PommTestSessionBuilder",
                        "pomm:default" => true,
                    ],
                ];
        }

        return $this->newTestedInstance($configuration);
    }

    public function testConstructor()
    {
        $pomm = $this->getPomm([]);
        $this
            ->assert("Empty constructor.")
            ->object($pomm)
            ->isTestedInstance()
            ->array($pomm->getSessionBuilders())
            ->isIdenticalTo([])
            ->assert("Constructor with parameters.")
            ->array(
                $this->newTestedInstance([
                    "first_db_config" => [
                        "dsn" => "pgsql://user:pass@host:port/db_name",
                    ],
                    "second_db_config" => [
                        "dsn" => "pgsql://user:pass@host:port/db_name",
                    ],
                ])->getSessionBuilders())
                ->size->isEqualTo(2)
                ->exception(function() { return $this->newTestedInstance(
                    [
                        "db_three" => [
                            "dsn" => "pgsql://user:pass@host:port/db_name",
                            "class:session_builder" => "\\Whatever\\Unexistent\\Class",
                        ],
                    ]); })
                ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
                ->message->contains('Could not instanciate')
                ;
    }

    public function testAddBuilder()
    {
        $pomm = $this->getPomm([]);
        $this
            ->assert("Set new session builder.")
            ->object($pomm->addBuilder('pika', $this->getSessionBuilder()))
            ->isInstanceOf('\PommProject\Foundation\Pomm')
            ->array(array_keys($pomm->addBuilder('pika', $this->getSessionBuilder())->getSessionBuilders()))
            ->isIdenticalTo(['pika'])
            ;
    }

    public function testGetBuilder()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getBuilder('db_one'))
            ->isInstanceOf('\PommProject\Foundation\Session\SessionBuilder')
            ->object($pomm->getBuilder('db_two'))
            ->isInstanceOf('\PommProject\Foundation\Test\Fixture\PommTestSessionBuilder')
            ->exception(function() use ($pomm) { $pomm->getBuilder('whatever'); })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains("No such builder")
            ;
    }

    public function testGetSession()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getSession('db_one'))
            ->isInstanceOf('\PommProject\Foundation\Session\Session')
            ->object($pomm->getSession('db_two'))
            ->isInstanceOf('\PommProject\Foundation\Test\Fixture\PommTestSession')
            ->exception(function() use ($pomm) { return $pomm->getSession('whatever'); })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains("{'db_one', 'db_two'}")
            ->array($pomm->getSession('db_one')->getRegisterPoolersNames())
            ->isIdenticalTo(['prepared_query', 'query_manager', 'converter', 'observer', 'inspector', 'listener'])
            ;
    }

    public function testCreateSession()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getSession('db_one'))
            ->isInstanceOf('\PommProject\Foundation\Session\Session')
            ->object($pomm->getSession('db_two'))
            ->isInstanceOf('\PommProject\Foundation\Test\Fixture\PommTestSession')
            ;
    }

    public function testPostConfiguration()
    {
        $pomm = $this
            ->getPomm()
            ->addPostConfiguration('db_two', function($session) { $session->getListener('pika'); });

        $this
            ->boolean($pomm['db_two']->hasClient('listener', 'pika'))
            ->isTrue()
            ;
    }

    public function testDefault()
    {
        $this
            ->exception(function() { return $this->newTestedInstance()->getDefaultSession(); })
            ->message->contains("No default session builder set.")
            ->object($this->getPomm()->getDefaultSession())
            ->isInstanceOf('\PommProject\Foundation\Session\Session')
            ->string($this->getPomm()->getDefaultSession()->getStamp())
            ->contains('db_two')
            ->string($this->newTestedInstance(['one' => ['dsn' => 'pgsql://user/db']])->getDefaultSession()->getStamp())
            ->contains('one')
            ->exception(function() { return $this->getPomm()->setDefaultBuilder('none'); })
            ->message->contains("No such builder")
            ->string($this->getPomm()->setDefaultBuilder('db_one')->getDefaultSession()->getStamp())
            ->contains('db_one')
            ;
    }

    public function testIsDefault()
    {
        $pomm = $this->newTestedInstance([
            'one' => ['dsn' => 'pgsql://user/db'],
            'two' => ['dsn' => 'pgsql://user/db']
        ]);

        $this
            ->boolean($pomm->isDefaultSession('one'))
            ->isTrue()
            ->boolean($pomm->isDefaultSession('two'))
            ->isFalse()
            ->boolean($pomm->isDefaultSession('three'))
            ->isFalse()
        ;
    }
}
