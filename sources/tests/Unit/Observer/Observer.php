<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Observer;

use PommProject\Foundation\Test\Unit\SessionAwareAtoum;
use PommProject\Foundation\Observer\ObserverPooler;

class Observer extends SessionAwareAtoum
{
    protected function registerClientPoolers()
    {
        $this->session
            ->registerClientPooler(new ObserverPooler())
            ;
    }

    protected function notify($channel, $data = null)
    {
        $connection = $this
            ->getSession()
            ->getConnection()
            ;

        $connection
            ->executeAnonymousQuery(
                sprintf(
                    "notify %s, %s",
                    $connection->escapeIdentifier($channel),
                    $connection->escapeLiteral($data)
                )
            );

        sleep(0.5);

        return $this;
    }

    public function testGetNotification()
    {
        $this
            ->getSession()
            ->registerClient($this->newTestedInstance('pika'))
            ;

        $this
            ->variable($this->getSession()->getObserver('pika')->getNotification())
            ->isNull()
            ;
        $this->notify('pika');
        $this
            ->array($this->getSession()->getObserver('pika')->getNotification())
            ->containsValues(['pika', ''])
            ;
        $this->notify('pika', 'chu');
        $this
            ->array($this->getSession()->getObserver('pika')->getNotification())
            ->containsValues(['pika', 'chu'])
            ->variable($this->getSession()->getObserver('pika')->getNotification())
            ->isNull()
            ;
    }

    public function testThrowNotification()
    {
        $this
            ->getSession()
            ->registerClient($this->newTestedInstance('an identifier'))
            ;
        $this
            ->object($this->getSession()->getObserver('an identifier')->throwNotification())
            ->isInstanceOf('\PommProject\Foundation\Observer\Observer')
            ;
        $this->notify('an identifier', 'some data');
        $this
            ->exception(function() { $this->getSession()->getObserver('an identifier')->throwNotification(); })
            ->message->contains('some data')
            ->object($this->getSession()->getObserver('an identifier')->throwNotification())
            ->isInstanceOf('\PommProject\Foundation\Observer\Observer')
            ;
    }
}
