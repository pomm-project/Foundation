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
        $string = <<<_
\\" ''\r
_;
        $this
            ->string($this->newTestedInstance()->fromPg($string, 'text', $this->getSession()))
            ->isEqualTo("\\\" ''\r")
            ->variable($this->newTestedInstance()->fromPg('NULL', 'text', $this->getSession()))
            ->isNull()
            ->string($this->newTestedInstance()->fromPg('', 'text', $this->getSession()))
            ->isEqualTo('')
            ;
    }

    public function testToPg()
    {
        $string = <<<_
\\"	!'
_;
        $this
            ->string($this->newTestedInstance()->toPg($string, 'varchar', $this->getSession()))
            ->isEqualTo("varchar  E'\\\\\"\t!'''")
            ->string($this->newTestedInstance()->toPg(null, 'varchar', $this->getSession()))
            ->isEqualTo('NULL::varchar')
            ->string($this->newTestedInstance()->toPg('', 'bpchar', $this->getSession()))
            ->isEqualTo("bpchar ''")
            ;
    }
}
