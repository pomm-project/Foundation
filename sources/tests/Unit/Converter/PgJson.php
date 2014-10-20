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

class PgJson extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $json  = <<<JSON
{"az": {"b": [" c ", "d"], "e": {"fé": "gù"}}, "h": ["'i'", "j"]}
JSON;
        $converter = $this->newTestedInstance();
        $this
            ->array(
                $converter
                ->fromPg(
                    $json,
                    'json',
                    $session
                )
            )
            ->isIdenticalTo(["az" => ['b' => [' c ', 'd'], 'e' => ['fé' => 'gù']], 'h' => ['\'i\'', 'j']])
            ->variable($converter->fromPg(null, 'json', $session))
        ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $data = ['a' => ['b' => [' c ', 'd'], 'e' => 'f'], 'g' => ['h', 'i']];
        $this
            ->string(
                $this
                    ->newTestedInstance()
                    ->toPg($data, 'json', $session)
                )
            ->isEqualTo('json \'{"a":{"b":[" c ","d"],"e":"f"},"g":["h","i"]}\'')
            ->string(
                $this
                    ->newTestedInstance()
                    ->toPg(null, 'json', $session)
                )
            ->isEqualTo('NULL::json')
            ;
    }
}

