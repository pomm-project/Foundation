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
        $session = $this->buildSession();
        $this
            ->array(
                $converter
                    ->fromPg('"a"=>"b", "b"=>NULL, "a b c"=>"d \'é\' f"', 'hstore', $session)
            )
            ->isIdenticalTo(['a' => 'b', 'b' => null, 'a b c' => 'd \'é\' f'])
            ->variable(
                $converter
                    ->fromPg(null, 'hstore', $session)
            )
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $session   = $this->buildSession();
        $converter = $this->newTestedInstance();
        $this
            ->string(
                $converter
                    ->toPg(null, 'hstore', $session)
                )
            ->isEqualTo('NULL::hstore')
            ->string(
                $converter
                    ->toPg(['a' => 'b', 'b' => null, 'a b c' => 'd \'é\' f'], 'hstore', $session)
                )
            ->isEqualTo('hstore(\'"a" => "b", "b" => NULL, "a b c" => "d \'\'é\'\' f"\')')
            ;
    }

    public function testToPgStandardFormat()
    {
        $session   = $this->buildSession();
        $converter = $this->newTestedInstance();
        $this
            ->variable(
                $converter
                    ->toPgStandardFormat(null, 'hstore', $session)
                )
            ->isNull()
            ->string(
                $converter
                    ->toPgStandardFormat(['a' => 'b', 'b' => null, 'a b c' => 'd \'é\' f'], 'hstore', $session)
                )
                ->isEqualTo('"""a"" => ""b"", ""b"" => NULL, ""a b c"" => ""d \'é\' f"""')
            ;
    }
}
