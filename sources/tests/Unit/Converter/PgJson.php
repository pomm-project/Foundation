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
                    $this->getSession()
                )
            )
            ->isIdenticalTo(["az" => ['b' => [' c ', 'd'], 'e' => ['fé' => 'gù']], 'h' => ['\'i\'', 'j']])
        ;
    }

    public function testToPg()
    {
        $data = ['a' => ['b' => [' c ', 'd'], 'e' => 'f'], 'g' => ['h', 'i']];
        $this
            ->string(
                $this
                    ->newTestedInstance()
                    ->toPg($data, 'json', $this->getSession())
                )
            ->isEqualTo('json \'{"a":{"b":[" c ","d"],"e":"f"},"g":["h","i"]}\'')
            ;
    }
}

