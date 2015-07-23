<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\QueryManager;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Test\Unit\Tester\VanillaSessionAtoum;

class QueryManagerPooler extends VanillaSessionAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClientPooler($this->newTestedInstance())
            ;
    }

    public function testGetClient()
    {
        $session = $this->buildSession();
        $this
            ->object(
                $session
                    ->getPoolerForType('query_manager')
                    ->getClient()
                )
            ->isInstanceOf('\PommProject\Foundation\QueryManager\SimpleQueryManager')
            ->exception(function () use ($session) {
                return
                    $session
                        ->getPoolerForType('query_manager')
                        ->getClient('\No\Such\Client')
                        ;
            })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains('Could not load')
            ;
    }
}
