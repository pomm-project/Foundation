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

class PgString extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $string = <<<_
\\" ''\r
_;
        $this
            ->string($this->newTestedInstance()->fromPg($string, 'text', $session))
            ->isEqualTo("\\\" ''\r")
            ->variable($this->newTestedInstance()->fromPg(null, 'text', $session))
            ->isNull()
            ->string($this->newTestedInstance()->fromPg(' ', 'text', $session))
            ->isEqualTo(' ')
            ->string($this->newTestedInstance()->fromPg('', 'text', $session))
            ->isEqualTo('')
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $string = <<<_
\\"	!'
_;
        $this
            ->string($this->newTestedInstance()->toPg($string, 'varchar', $session))
            ->isEqualTo("varchar  E'\\\\\"\t!'''")
            ->string($this->newTestedInstance()->toPg(null, 'varchar', $session))
            ->isEqualTo('NULL::varchar')
            ->string($this->newTestedInstance()->toPg('', 'bpchar', $session))
            ->isEqualTo("bpchar ''")
            ;
    }
}
