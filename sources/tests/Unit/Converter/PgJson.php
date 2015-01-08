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
        $object = $this->newTestedInstance(false)->fromPg($json, 'json', $session);
        $this
            ->object($object)
            ->isInstanceOf((object) [])
            ->object($object->az)
            ->array($object->az->b)
            ->isIdenticalTo([' c ', 'd'])
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $converter = $this->newTestedInstance();
        $data = ['a' => ['b' => [' c ', 'd'], 'e' => 'f'], 'g' => ['h', 'i']];
        $this
            ->string(
                $converter
                    ->toPg($data, 'json', $session)
                )
            ->isEqualTo('json \'{"a":{"b":[" c ","d"],"e":"f"},"g":["h","i"]}\'')
            ->string(
                $converter
                    ->toPg(null, 'json', $session)
                )
            ->isEqualTo('NULL::json')
            ;
    }

    public function testToCsv()
    {
        $session = $this->buildSession();
        $data = ['a' => ['b' => [' c ', 'd'], 'e' => 'f'], 'g' => ['h', 'i']];
        $this
            ->string(
                $this
                    ->newTestedInstance()
                    ->toCsv($data, 'json', $session)
                )
            ->isEqualTo('"{""a"":{""b"":["" c "",""d""],""e"":""f""},""g"":[""h"",""i""]}"')
            ->variable(
                $this
                    ->newTestedInstance()
                    ->toCsv(null, 'json', $session)
                )
            ->isNull()
            ;
    }
}

