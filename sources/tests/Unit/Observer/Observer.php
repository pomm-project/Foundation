<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Observer;

use PommProject\Foundation\Tester\VanillaSessionAtoum;
use PommProject\Foundation\Observer\ObserverPooler;
use PommProject\Foundation\Session\Session;

class Observer extends VanillaSessionAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClientPooler(new ObserverPooler())
            ;
    }

    protected function notify($channel, $data = null)
    {
        $session    = $this->buildSession();
        $connection = $session->getConnection();

        $connection
            ->executeAnonymousQuery(
                sprintf(
                    "notify %s, %s",
                    $connection->escapeIdentifier($channel),
                    $connection->escapeLiteral($data)
                )
            );

        sleep(1);

        return $this;
    }

    public function testGetNotification()
    {
        $session = $this->buildSession()
            ->registerClient($this->newTestedInstance('pika'))
            ;

        $this
            ->variable($session->getObserver('pika')->getNotification())
            ->isNull()
            ;
        $this->notify('pika');
        $this
            ->array($session->getObserver('pika')->getNotification())
            ->containsValues(['pika', ''])
            ;
        $this->notify('pika', 'chu');
        $this
            ->array($session->getObserver('pika')->getNotification())
            ->containsValues(['pika', 'chu'])
            ->variable($session->getObserver('pika')->getNotification())
            ->isNull()
            ;
    }

    public function testThrowNotification()
    {
        $session = $this->buildSession()
            ->registerClient($this->newTestedInstance('an identifier'))
            ;
        $this
            ->object($session->getObserver('an identifier')->throwNotification())
            ->isInstanceOf(\PommProject\Foundation\Observer\Observer::class)
            ;
        $this->notify('an identifier', 'some data');
        $this
            ->exception(function () use ($session) { $session->getObserver('an identifier')->throwNotification(); })
            ->message->contains('some data')
            ->object($session->getObserver('an identifier')->throwNotification())
            ->isInstanceOf(\PommProject\Foundation\Observer\Observer::class)
            ;
    }
}
