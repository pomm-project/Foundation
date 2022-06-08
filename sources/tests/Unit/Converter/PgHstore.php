<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Converter\PgHstore as PommHstore;

class PgHstore extends BaseConverter
{
    protected function initializeSession(Session $session)
    {
        parent::initializeSession($session);

        $session
            ->getPoolerForType('converter')
            ->getConverterHolder()
            ->registerConverter('hstore', new PommHstore(), ['hstore', 'public.hstore'])
            ;
    }

    public function testFromPg()
    {
        $converter = $this->newTestedInstance();
        $session = $this->buildSession();
        $this
            ->array(
                $converter
                    ->fromPg('"a"=>"b", "b"=>NULL, "a \\\\b\\\\ c"=>"d \'é\' f"', 'hstore', $session)
            )
            ->isIdenticalTo(['a' => 'b', 'b' => null, 'a \\b\\ c' => 'd \'é\' f'])
            ->array(
                $converter
                ->fromPg('"pika"=>"\\"chu, rechu"', 'hstore', $session)
            )
            ->isIdenticalTo(['pika' => '"chu, rechu'])
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
            ->isEqualTo('hstore($hs$"a" => "b", "b" => NULL, "a b c" => "d \'é\' f"$hs$)')
            ->exception(function () use ($session, $converter) { $converter->toPg('foo', 'hstore', $session); })
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->message->contains('Array converter data must be an array')
            ;
    }

    public function testToPgStandardFormat()
    {
        $session   = $this->buildSession();
        $converter = $this->newTestedInstance();
        $hstore    = ['a' => 'b', 'b' => null, 'a \b\ c' => 'd \'é\' f'];
        $this
            ->variable(
                $converter
                    ->toPgStandardFormat(null, 'hstore', $session)
                )
            ->isNull()
            ->string(
                $converter
                    ->toPgStandardFormat($hstore, 'hstore', $session)
                )
            ->isEqualTo('"a" => "b", "b" => NULL, "a \\\\b\\\\ c" => "d \'é\' f"')
            ->exception(function () use ($session, $converter) { $converter->toPgStandardFormat('foo', 'hstore', $session); })
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->message->contains('Array converter data must be an array')

        ;
        if ($this->doesTypeExist('hstore', $session) === false) {
            $this->skip("HSTORE extension is not installed, skipping tests.");

            return;
        }

        $this
            ->array($this->sendToPostgres($hstore, 'hstore', $session))
            ->isIdenticalTo($hstore)
            ;
    }
}
