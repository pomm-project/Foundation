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
use PommProject\Foundation\Converter\Type\NumRange;
use PommProject\Foundation\Tester\FoundationSessionAtoum;
use PommProject\Foundation\PreparedQuery\PreparedQuery as testedClass;

class PreparedQuery extends FoundationSessionAtoum
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

    public function testExecute()
    {
        $session = $this->buildSession();
        $sql = <<<SQL
select
  p.id, p.pika, p.a_timestamp, p.a_range
from (values
    (1, 'one', '1999-08-08'::timestamp, numrange(1.3, 1.8)),
    (2, 'two', '2000-09-07'::timestamp, numrange(1.1, 1.5)),
    (3, 'three', '2001-10-25 15:43'::timestamp, numrange(1.6, 1.9)),
    (4, 'four', '2002-01-01 01:10'::timestamp, numrange(1.8, 2.3))
) p (id, pika, a_timestamp, a_range)
where (p.id >= $* or p.pika = $*) and p.a_timestamp > $*::timestamp and p.a_range && $*::numrange
SQL;
        $query = $this->newTestedInstance($sql);
        $session->registerClient($query);
        $result = $query->execute([2, 'three', new \DateTime('2000-01-01'), new NumRange('[1.0, 1.7)')]);

        $this
            ->integer($result->countRows())
            ->isEqualTo(2)
            ;
    }
}

