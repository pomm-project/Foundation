<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Listener;

use PommProject\Foundation\Tester\FoundationSessionAtoum;
use PommProject\Foundation\Session\Session;
use Mock\PommProject\Foundation\Listener\Listener as MockListener;

class ListenerPooler extends FoundationSessionAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClient(new MockListener('pika'))
            ->registerClient(new MockListener('chu'))
            ;
    }

    public function testNotify()
    {
        $session = $this->buildSession();
        $session->registerClientPooler($this->newTestedInstance());

        $this
            ->assert('notifying one listener.')
            ->object(
                $session
                    ->getPoolerForType('listener')
                    ->notify('pika', [ 'data' => 1 ])
                )
            ->isInstanceOf(\PommProject\Foundation\Listener\ListenerPooler::class)
            ->mock($session->getClient('listener', 'pika'))
            ->call('notify')
            ->once()
            ->mock($session->getClient('listener', 'chu'))
            ->call('notify')
            ->never()
            ->assert('notifying lot of listeners.')
            ->object(
                $session
                    ->getPoolerForType('listener')
                    ->notify(['pika', 'chu', 'whatever'], [ 'data' => 1 ])
            )
            ->mock($session->getClient('listener', 'pika'))
            ->call('notify')
            ->once()
            ->mock($session->getClient('listener', 'chu'))
            ->call('notify')
            ->once()
            ->assert('notifying all listeners.')
            ->object(
                $session
                    ->getPoolerForType('listener')
                    ->notify('*', [ 'data' => 1 ])
            )
            ->isInstanceOf(\PommProject\Foundation\Listener\ListenerPooler::class)
            ->mock($session->getClient('listener', 'pika'))
            ->call('notify')
            ->once()
            ->mock($session->getClient('listener', 'chu'))
            ->call('notify')
            ->once()
            ->assert('notifying a listeners with subspace.')
            ->object(
                $session
                    ->getPoolerForType('listener')
                    ->notify('pika:plop', [ 'data' => 1 ])
                )
            ->isInstanceOf(\PommProject\Foundation\Listener\ListenerPooler::class)
            ->mock($session->getClient('listener', 'pika'))
            ->call('notify')
            ->once()
            ->mock($session->getClient('listener', 'chu'))
            ->call('notify')
            ->never()
            ;
    }
}
