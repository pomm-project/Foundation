<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgLtree extends BaseConverter
{
    public function testFromPg()
    {
        $converter = $this->newTestedInstance();
        $this
            ->array(
                $converter
                    ->fromPg('_a_b_.c.d', 'ltree', $this->getSession())
            )
            ->isIdenticalTo(['_a_b_', 'c', 'd'])
            ->variable($converter->fromPg('NULL', 'ltre', $this->getSession()))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $converter = $this->newTestedInstance();
        $this
            ->string(
                $converter
                    ->toPg(['_a_b_', 'c', 'd'], 'ltree', $this->getSession())
            )
            ->isEqualTo("ltree '_a_b_.c.d'")
            ->string($converter->toPg(null, 'ltree', $this->getSession()))
            ->isEqualTo('NULL')
        ;
    }
}
