<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Session;

use PommProject\Foundation\Session\Session                   as VanillaSession;
use PommProject\Foundation\Session\Connection                as FoundationConnection;
use PommProject\Foundation\Tester\VanillaSessionAtoum;
use Mock\PommProject\Foundation\Client\ClientInterface       as ClientInterfaceMock;
use Mock\PommProject\Foundation\Client\ClientPoolerInterface as ClientPoolerInterfaceMock;

class Session extends VanillaSessionAtoum
{
    protected function initializeSession(VanillaSession $session)
    {
    }

    protected function getClientInterfaceMock($identifier)
    {
        $client = new ClientInterfaceMock();
        $client->getMockController()->getClientType = 'test';
        $client->getMockController()->getClientIdentifier = $identifier;

        return $client;
    }

    protected function getClientPoolerInterfaceMock($type)
    {
        $client_pooler = new ClientPoolerInterfaceMock();
        $client_pooler->getMockController()->getPoolerType = $type;
        $client_pooler->getMockController()->getClient = $this->getClientInterfaceMock('ok');

        return $client_pooler;
    }

    public function testGetStamp()
    {
        $this
            ->variable($this->buildSession()->getStamp())
            ->isNull
            ->string($this->buildSession('a stamp')->getStamp())
            ->isEqualTo('a stamp')
            ;
    }

    public function testGetConnection()
    {
        $session = $this->buildSession();

        $this
            ->object($session->getConnection())
            ->isInstanceOf(\PommProject\Foundation\Session\Connection::class)
            ;
    }

    public function testGetClient()
    {
        $session = $this->buildSession();
        $client  = $this->getClientInterfaceMock('one');
        $session->registerClient($client);
        $this
            ->variable($session->getClient('test', 'two'))
            ->isNull()
            ->object($session->getClient('test', 'one'))
            ->isIdenticalTo($client)
            ->variable($session->getClient('whatever', 'two'))
            ->isNull()
            ->variable($session->getClient(null, 'two'))
            ->isNull()
            ;
    }

    public function testRegisterClient()
    {
        $session     = $this->buildSession();
        $client_mock = $this->getClientInterfaceMock('one');

        $this
            ->variable($session->getClient('test', 'one'))
            ->isNull()
            ->object($session->registerClient($client_mock))
            ->isInstanceOf(\PommProject\Foundation\Session\Session::class)
            ->mock($client_mock)
            ->call('getClientIdentifier')
            ->once()
            ->call('getClientType')
            ->once()
            ->call('initialize')
            ->once()
            ->object($session->getClient('test', 'one'))
            ->isIdenticalTo($client_mock)
            ;
    }

    public function testRegisterPooler()
    {
        $session            = $this->buildSession();
        $client_pooler_mock = $this->getClientPoolerInterfaceMock('test');

        $this
            ->boolean($session->hasPoolerForType('test'))
            ->isFalse()
            ->assert('Testing client pooler registration.')
            ->object($session->registerClientPooler($client_pooler_mock))
            ->isInstanceOf(\PommProject\Foundation\Session\Session::class)
            ->boolean($session->hasPoolerForType('test'))
            ->isTrue()
            ->mock($client_pooler_mock)
            ->call('getPoolerType')
            ->atLeastOnce()
            ->call('register')
            ->once()
            ;
    }

    public function testGetPoolerForType()
    {
        $session            = $this->buildSession();
        $client_pooler_mock = $this->getClientPoolerInterfaceMock('test');

        $this
            ->exception(function () use ($session) { $session->getPoolerForType('test'); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('No pooler registered for type')
            ->object($session
                ->registerClientPooler($client_pooler_mock)
                ->getPoolerForType('test')
            )
            ->isIdenticalTo($client_pooler_mock)
            ;
    }

    public function testGetClientUsingPooler()
    {
        $client_pooler_mock = $this->getClientPoolerInterfaceMock('test');
        $session            = $this->buildSession()->registerClientPooler($client_pooler_mock);

        $this
            ->object($session->getClientUsingPooler('test', 'ok'))
            ->isInstanceOf(\PommProject\Foundation\Client\ClientInterface::class)
            ->exception(function () use ($session) {$session->getClientUsingPooler('whatever', 'ok');})
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('No pooler registered for type')
            ;
    }

    public function testUnderscoreCall()
    {
        $client_pooler_mock = $this->getClientPoolerInterfaceMock('test');
        $session            = $this->buildSession()->registerClientPooler($client_pooler_mock);

        $this
            ->exception(function () use ($session) { $session->azerty('ok', 'what'); })
            ->isInstanceOf(\BadFunctionCallException::class)
            ->message->contains('Unknown method')
            ->exception(function () use ($session) { $session->getPika('ok'); })
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('No pooler registered for type')
            ->object($session->getTest('ok'))
            ->isInstanceOf(\PommProject\Foundation\Client\ClientInterface::class)
            ->mock($client_pooler_mock)
            ->call('getClient')
            ->withArguments('ok')
            ->once()
            ;
    }

    public function testShutdown()
    {
        $client_pooler_mock = $this->getClientPoolerInterfaceMock('test');
        $session            = $this->buildSession()->registerClientPooler($client_pooler_mock);
        $session->shutdown();

        $this
            ->exception(fn() => $session->getTest('ok'))
            ->isInstanceOf(\PommProject\Foundation\Exception\FoundationException::class)
            ->message->contains('is shutdown')
            ->integer($session->getConnection()->getConnectionStatus())
            ->isEqualTo(FoundationConnection::CONNECTION_STATUS_NONE)
            ;
        $session = $this->buildSession();
        $session->getConnection()->executeAnonymousQuery('select true');
        $session->shutdown();
        $this
            ->integer($session->getConnection()->getConnectionStatus())
            ->isEqualTo(FoundationConnection::CONNECTION_STATUS_CLOSED)
            ;
    }
}
