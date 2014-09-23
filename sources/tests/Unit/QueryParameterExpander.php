<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\QueryParameterExpander as PommParameterExpander;
use Atoum;

class QueryParameterExpander extends Atoum
{
    public function testUnorder()
    {
        $this
            ->string(PommParameterExpander::unorder('a = $1, b >= $2, pika($3)'))
            ->isEqualTo('a = $*, b >= $*, pika($*)')
            ->string(PommParameterExpander::unorder('$$$1$*2$2$ 3$'))
            ->isEqualTo('$$$*$*2$*$ 3$')
        ;
    }

    public function testOrder()
    {
        $this
            ->string(PommParameterExpander::order('a = $*, b >= $*, pika($*)'))
            ->isEqualTo('a = $1, b >= $2, pika($3)')
            ->string(PommParameterExpander::order('$* $* $* $* $* $* $* $* $* $* $*'))
            ->isEqualTo('$1 $2 $3 $4 $5 $6 $7 $8 $9 $10 $11')
            ;
    }
}
