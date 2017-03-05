<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

class Pager extends FoundationSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

    protected function getResultIterator($end, $start = 1)
    {
        return $this
            ->buildSession()
            ->getQueryManager()
            ->query("SELECT generate_series($*::int4, $*::int4) AS row", [$start, $end])
            ;
    }

    public function testGetters()
    {
        $this
            ->assert("The getIterator method returns the iterator given in the constructor.")
            ->given($iterator = $this->getResultIterator(10))
            ->and()
            ->given($pager = $this->newTestedInstance($iterator, 100, 10, 1))
                ->object($pager->getIterator())
                ->isInstanceOf('\PommProject\Foundation\ConvertedResultIterator')
                ->isIdenticalTo($iterator)
            ->assert("The getCount method returns the total count given in the constructor.")
                ->integer($pager->getCount())
                ->isEqualTo(100)
            ->assert("The getResultCount method returns the count of results in the current page.")
                ->integer($pager->getResultCount())
                ->isEqualTo($iterator->count())
            ->assert("The getPage method returns the pager given in the constructor.")
                ->integer($pager->getPage())
                ->isEqualTo(1)
                ;
    }

    public function testSinglePageResultSet()
    {
        $this
            ->assert('Test stats when the pager is a one pager.')
            ->given($iterator = $this->getResultIterator(3))
            ->and()
            ->given($pager = $this->newTestedInstance($iterator, 3, 10, 1))
                ->integer($pager->getResultMin())
                    ->isEqualTo(1)
                ->integer($pager->getResultMax())
                    ->isEqualTo(3)
                ->integer($pager->getLastPage())
                    ->isEqualTo(1)
                ->integer($pager->getMaxPerPage())
                    ->isEqualTo(10)
                ->boolean($pager->isPreviousPage())
                    ->isFalse()
                ->boolean($pager->isNextPage())
                    ->isFalse()
            ;
    }

    public function testSingleFullPageResultSet()
    {
        $this
            ->assert("Test stats when the pager is one full page.")
            ->given($iterator = $this->getResultIterator(10))
            ->and()
            ->given($pager = $this->newTestedInstance($iterator, 10, 10, 1))
                ->integer($pager->getResultMin())
                    ->isEqualTo(1)
                ->integer($pager->getResultMax())
                    ->isEqualTo(10)
                ->integer($pager->getLastPage())
                    ->isEqualTo(1)
                ->integer($pager->getMaxPerPage())
                    ->isEqualTo(10)
                ->boolean($pager->isPreviousPage())
                    ->isFalse()
                ->boolean($pager->isNextPage())
                    ->isFalse()
            ;
    }

    public function testJustOverOnePage()
    {
        $this
            ->assert("Test stats when the pager is one result after full page.")
            ->given($iterator = $this->getResultIterator(11, 11))
            ->and()
            ->given($pager = $this->newTestedInstance($iterator, 11, 10, 2))
                ->integer($pager->getResultMin())
                    ->isEqualTo(11)
                ->integer($pager->getResultMax())
                    ->isEqualTo(11)
                ->integer($pager->getLastPage())
                    ->isEqualTo(2)
                ->integer($pager->getMaxPerPage())
                    ->isEqualTo(10)
                ->boolean($pager->isPreviousPage())
                    ->isTrue()
                ->boolean($pager->isNextPage())
                    ->isFalse()
            ;
    }

    public function testEmptyIteratorPager()
    {
        $this
            ->assert("Test stats when the pager fed with an empty iterator.")
            ->given($iterator = $this->getResultIterator(0))
            ->and()
            ->given($pager = $this->newTestedInstance($iterator, 0, 10, 1))
                ->integer($pager->getResultMin())
                    ->isEqualTo(0)
                ->integer($pager->getResultMax())
                    ->isEqualTo(0)
                ->integer($pager->getLastPage())
                    ->isEqualTo(1)
                ->integer($pager->getMaxPerPage())
                    ->isEqualTo(10)
                ->boolean($pager->isPreviousPage())
                    ->isFalse()
                ->boolean($pager->isNextPage())
                    ->isFalse()
            ;
    }
}
