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

class PgHstore extends BaseConverter
{
    public function testFromPg()
    {
        $converter = $this->newTestedInstance();
        $this
            ->array(
                $converter
                    ->fromPg('"a"=>"b", "b"=>NULL, "a b c"=>"d \'é\' f"', 'hstore', $this->getSession())
            )
            ->isIdenticalTo(['a' => 'b', 'b' => null, 'a b c' => 'd \'é\' f'])
            ->variable(
                $converter
                    ->fromPg(null, 'hstore', $this->getSession())
            )
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $converter = $this->newTestedInstance();
        $this
            ->string(
                $converter
                    ->toPg(null, 'hstore', $this->getSession())
                )
            ->isEqualTo('NULL::hstore')
            ->string(
                $converter
                    ->toPg(['a' => 'b', 'b' => null, 'a b c' => 'd \'é\' f'], 'hstore', $this->getSession())
                )
            ->isEqualTo('hstore(\'"a" => "b", "b" => NULL, "a b c" => "d \'\'é\'\' f"\')')
            ;
    }
}
