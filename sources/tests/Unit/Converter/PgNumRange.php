<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Converter\Type\NumRange;

class PgNumRange extends BaseConverter
{
    public function testFromPg()
    {
        $this
            ->object($this->newTestedInstance()->fromPg('[1,3)', 'int4range', $this->getSession()))
            ->isInstanceOf('PommProject\Foundation\Converter\Type\NumRange')
            ->variable($this->newTestedInstance()->fromPg(null, 'point', $this->getSession()))
            ->isNull()
            ;
        $range = $this->newTestedInstance()->fromPg('[1,3)', 'int4range', $this->getSession());
        $this
            ->integer($range->start_limit)
            ->isEqualTo(1)
            ->integer($range->end_limit)
            ->isEqualTo(3)
            ->boolean($range->start_incl)
            ->isTrue()
            ->boolean($range->end_incl)
            ->isFalse()
            ;
        $range = $this->newTestedInstance()->fromPg('(-3.1415, -1.6180]', 'numrange', $this->getSession());
        $this
            ->float($range->start_limit)
            ->isEqualTo(-3.1415)
            ->float($range->end_limit)
            ->isEqualTo(-1.618)
            ->boolean($range->start_incl)
            ->isFalse()
            ->boolean($range->end_incl)
            ->isTrue()
            ;
    }

    public function testToPg()
    {
        $range = $this->newTestedInstance()->fromPg('[1,3)', 'numrange', $this->getSession());
    }
}

