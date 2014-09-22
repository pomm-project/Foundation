<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\Inflector as PommInflector;
use Atoum;

class Inflector extends Atoum
{
    public function testStudlyCaps()
    {
        $this
            ->variable(PommInflector::studlyCaps(null))
            ->isNull()
            ->string(PommInflector::studlyCaps(''))
            ->length->isEqualTo(0)
            ->string(PommInflector::studlyCaps('one'))
            ->isEqualTo('One')
            ->string(PommInflector::studlyCaps('oNe'))
            ->isEqualTo('One')
            ->string(PommInflector::studlyCaps('one_two'))
            ->isEqualTo('OneTwo')
            ->string(PommInflector::studlyCaps('one_tWo'))
            ->isEqualTo('OneTwo')
            ->string(PommInflector::studlyCaps('two_three_four'))
            ->isEqualTo('TwoThreeFour')
            ->string(PommInflector::studlyCaps('one__two'))
            ->isEqualTo('One_Two')
            ->string(PommInflector::studlyCaps('one_2_three'))
            ->isEqualTo('One_2Three')
            ->string(PommInflector::studlyCaps('one2_three'))
            ->isEqualTo('One2Three')
            ->string(PommInflector::studlyCaps('_one'))
            ->isEqualTo('One')
            ;
    }

    public function testUnderscore()
    {
        $this
            ->variable(PommInflector::underscore(null))
            ->isNull()
            ->string(PommInflector::underscore(''))
            ->length->isEqualTo(0)
            ->string(PommInflector::underscore('one'))
            ->isEqualTo('one')
            ->string(PommInflector::underscore('oneTwo'))
            ->isEqualTo('one_two')
            ->string(PommInflector::underscore('twoThreeFour'))
            ->isEqualTo('two_three_four')
            ->string(PommInflector::underscore('one_Two'))
            ->isEqualTo('one__two')
            ->string(PommInflector::underscore('one2Three'))
            ->isEqualTo('one2_three')
            ->string(PommInflector::underscore('one_2Three'))
            ->isEqualTo('one_2_three')
            ->string(PommInflector::underscore('One'))
            ->isEqualTo('one')
            ;
    }
}
