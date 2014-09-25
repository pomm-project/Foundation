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

use PommProject\Foundation\DatabaseConfiguration;
use PommProject\Foundation\Session;
use Atoum;

class Pomm extends Atoum
{
    protected function getDatabaseConfiguration(array $config = [])
    {
        //$controller = new \Atoum\Mock\Controller();
       // $controller->__construct = function($config) {};

        return new DatabaseConfiguration($config);
    }

    protected function getPomm(array $configuration = null)
    {
        if ($configuration === null) {
            $configuration = [
                "db_one"   => ["dsn" => "pgsql://user:pass@host:port/db_name"],
                "db_two"   => [
                    "dsn" => "pgsql://user:pass@host:port/db_name",
                    "config_class_name" => "PommProject\Foundation\Test\Unit\PommTestDatabaseConfiguration",
                ],
                "db_three"   => [
                    "dsn" => "pgsql://user:pass@host:port/db_name",
                    "config_class_name" => "\\Whatever\\Unexistent\\Class",
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
            ->array($pomm->getConfigurations())
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
                ])->getConfigurations())
                ->size->isEqualTo(2)
                ;
    }

    public function testSetConfiguration()
    {
        $pomm = $this->getPomm([]);
        $this
            ->assert("Set new database configuration.")
            ->object($pomm->setConfiguration('pika', $this->getDatabaseConfiguration()))
            ->isInstanceOf('\PommProject\Foundation\Pomm')
            ->array(array_keys($pomm->setConfiguration('pika', $this->getDatabaseConfiguration())->getConfigurations()))
            ->isIdenticalTo(['pika'])
            ;
    }

    public function testGetConfiguration()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getConfiguration('db_one'))
            ->isInstanceOf('\PommProject\Foundation\DatabaseConfiguration')
            ->object($pomm->getConfiguration('db_two'))
            ->isInstanceOf('PommProject\Foundation\Test\Unit\PommTestDatabaseConfiguration')
            ->exception(function() use ($pomm) { $pomm->getConfiguration('db_three'); })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains('could not be loaded')
            ->exception(function() use ($pomm) { $pomm->getConfiguration('whatever'); })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains("not found")
            ;
    }

    public function testGetSession()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getSession('db_one'))
            ->isInstanceOf('\PommProject\Foundation\Session')
            ->object($pomm->getSession('db_two'))
            ->isInstanceOf('\PommProject\Foundation\Test\Unit\PommTestSession')
            ;
    }

    public function testCreateSession()
    {
        $pomm = $this->getPomm();
        $this
            ->object($pomm->getSession('db_one'))
            ->isInstanceOf('\PommProject\Foundation\Session')
            ->object($pomm->getSession('db_two'))
            ->isInstanceOf('\PommProject\Foundation\Test\Unit\PommTestSession')
            ;
    }
}

class PommTestDatabaseConfiguration extends \PommProject\Foundation\DatabaseConfiguration
{
    public function __construct(array $configuration)
    {
        parent::__construct($configuration);
        $this
            ->getParameterHolder()
            ->setDefaultValue('class:session', '\PommProject\Foundation\Test\Unit\PommTestSession')
            ;
    }
}

class PommTestSession extends \PommProject\Foundation\Session
{
}
