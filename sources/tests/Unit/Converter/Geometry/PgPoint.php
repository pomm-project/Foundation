<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter\Geometry;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Converter\Type\Point;

class PgPoint extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $this
            ->object($this->newTestedInstance()->fromPg('(1.2345,-9.87654)', 'point', $session))
            ->isInstanceOf(\PommProject\Foundation\Converter\Type\Point::class)
            ->variable($this->newTestedInstance()->fromPg(null, 'point', $session))
            ->isNull()
            ;
        $point = $this->newTestedInstance()->fromPg('(1.2345,-9.87654)', 'point', $session);
        $this
            ->float($point->x)
            ->isEqualTo(1.2345)
            ->float($point->y)
            ->isEqualTo(-9.87654)
            ;
    }

    public function testToPg()
    {
        $session = $this->buildSession();
        $point = new Point('(1.2345, -9.87654)');
        $this
            ->string($this->newTestedInstance()->toPg($point, 'point', $session))
            ->isEqualTo('point(1.2345,-9.87654)')
            ->string($this->newTestedInstance()->toPg('(1.2345,-9.87654)', 'point', $session))
            ->isEqualTo('point(1.2345,-9.87654)')
            ->exception(fn() => $this->newTestedInstance()->toPg('azsdf', 'point', $session))
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->string($this->newTestedInstance()->toPg(null, 'subpoint', $session))
            ->isEqualTo('NULL::subpoint')
            ;
    }

    public function testToPgStandardFormat()
    {
        $session = $this->buildSession();
        $point = new Point('(1.2345, -9.87654)');
        $this
            ->string($this->newTestedInstance()->toPgStandardFormat($point, 'point', $session))
            ->isEqualTo('(1.2345,-9.87654)')
            ->string($this->newTestedInstance()->toPgStandardFormat('(1.2345,-9.87654)', 'point', $session))
            ->isEqualTo('(1.2345,-9.87654)')
            ->exception(fn() => $this->newTestedInstance()->toPgStandardFormat('azsdf', 'point', $session))
            ->isInstanceOf(\PommProject\Foundation\Exception\ConverterException::class)
            ->variable($this->newTestedInstance()->toPgStandardFormat(null, 'subpoint', $session))
            ->isNull()
            ;
    }
}
