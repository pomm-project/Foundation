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

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Converter\Type\Circle;
use PommProject\Foundation\Tester\FoundationSessionAtoum;
use PommProject\Foundation\PreparedQuery\PreparedQuery as TestedPreparedQuery;

class PreparedQuery extends FoundationSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

    public function testConstruct()
    {
        $this
            ->exception(function () { $this->newTestedInstance(null); })
            ->isInstanceOf(FoundationException::class)
            ->message->contains('empty query')
            ->object($this->newTestedInstance('abcd'))
            ->isInstanceOf(TestedPreparedQuery::class)
            ->string($this->newTestedInstance('abcd')->getClientIdentifier())
            ->isEqualTo(TestedPreparedQuery::getSignatureFor('abcd'))
            ;
    }

    public function testExecute()
    {
        $session = $this->buildSession();
        $sql = <<<SQL
select
  p.id, p.pika, p.a_timestamp, p.a_point
from (values
    (1, 'one', '1999-08-08'::timestamp, ARRAY[point(1.3, 1.6)]),
    (2, 'two', '2000-09-07'::timestamp, ARRAY[point(1.5, 1.5)]),
    (3, 'three', '2001-10-25 15:43'::timestamp, ARRAY[point(1.6, 1.4)]),
    (4, 'four', '2002-01-01 01:10'::timestamp, ARRAY[point(1.8, 2.3)])
) p (id, pika, a_timestamp, a_point)
where (p.id >= $* or p.pika = ANY($*::text[])) and p.a_timestamp > $*::timestamp and $*::pg_catalog."circle" @> ANY (p.a_point)
SQL;
        $query = $this->newTestedInstance($sql);
        $session->registerClient($query);
        $result = $query->execute([2, ['pika, chu', 'three'], new \DateTime('2000-01-01'), new Circle('<(1.5,1.5), 0.3>')]);

        $this
            ->integer($result->countRows())
            ->isEqualTo(2)
            ;
    }
}
