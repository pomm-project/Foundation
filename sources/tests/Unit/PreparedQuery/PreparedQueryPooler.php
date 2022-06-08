<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\PreparedQuery;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Tester\VanillaSessionAtoum;

class PreparedQueryPooler extends VanillaSessionAtoum
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
        $sql = 'select true where $*::boolean';
        $query = $session
                    ->getPoolerForType('prepared_query')
                    ->getClient($sql)
                    ;
        $this
            ->object($query)
            ->isInstanceOf(\PommProject\Foundation\PreparedQuery\PreparedQuery::class)
            ->string($query->getSql())
            ->isEqualTo($sql)
            ->object($session->getClientUsingPooler('prepared_query', $sql))
            ->isEqualTo($query)
            ;
    }
}
