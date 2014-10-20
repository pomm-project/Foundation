<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Query;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Test\Fixture\QueryListener;
use PommProject\Foundation\Tester\VanillaSessionAtoum;
use PommProject\Foundation\PreparedQuery\PreparedQueryPooler;

class QueryPooler extends VanillaSessionAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClientPooler($this->newTestedInstance())
            ->registerClientPooler(new PreparedQueryPooler)
            ;
    }

    public function testGetClient()
    {
        $session = $this->buildSession();
        $this
            ->object(
                $session
                    ->getPoolerForType('query')
                    ->getClient()
                )
            ->isInstanceOf('\PommProject\Foundation\Query\SimpleQuery')
            ->exception(function() use ($session) {
                return
                    $session
                        ->getPoolerForType('query')
                        ->getClient('\No\Such\Client')
                        ;
            })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains('Could not load')
            ;
    }

    public function testRegisterListener()
    {
        $session = $this->buildSession();
        $listener = new QueryListener();
        $session
            ->getClientUsingPooler('query', null)
            ;
        $session
            ->getPoolerForType('query')
            ->registerListener($listener)
            ;
        $session
            ->getClientUsingPooler('query', null)
            ->query('select generate_series(1, 5) as pika')
            ;
        $this
            ->integer($listener->getCounter())
            ->isEqualTo(2)
            ;
        $listener->clean();
        $session
            ->getClientUsingPooler('query', '\PommProject\Foundation\PreparedQuery\PreparedQueryQuery')
            ->query('select generate_series(1, 10) as pika')
            ;
        $this
            ->integer($listener->getCounter())
            ->isEqualTo(2)
            ->integer($listener->getLastEventData()['result_count'])
            ->isEqualTo(10)
            ;
    }
}
