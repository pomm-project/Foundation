<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\PreparedQuery;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Tester\VanillaSessionAtoum;
use PommProject\Foundation\PreparedQuery\PreparedQuery as testedClass;

class PreparedQuery extends VanillaSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

    public function testConstruct()
    {
        $this
            ->exception(function() { $this->newTestedInstance(null); })
            ->isInstanceOf('\PommProject\Foundation\Exception\FoundationException')
            ->message->contains('empty query')
            ->object($this->newTestedInstance('abcd'))
            ->isInstanceOf('\PommProject\Foundation\PreparedQuery\PreparedQuery')
            ->string($this->newTestedInstance('abcd')->getClientIdentifier())
            ->isEqualTo(testedClass::getSignatureFor('abcd'))
            ;
    }
}

