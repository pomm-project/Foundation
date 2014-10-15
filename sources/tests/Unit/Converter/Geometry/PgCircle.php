<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter\Geometry;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;
use PommProject\Foundation\Converter\Type\Circle;

class PgCircle extends BaseConverter
{
    public function testFromPg()
    {
        $this
            ->object($this->newTestedInstance()->fromPg('<(1.2345,-9.87654),3.141596>', 'circle', $this->getSession()))
            ->isInstanceOf('PommProject\Foundation\Converter\Type\Circle')
            ->variable($this->newTestedInstance()->fromPg(null, 'circle', $this->getSession()))
            ->isNull()
            ;
        $circle = $this->newTestedInstance()->fromPg('<(1.2345,-9.87654),3.141596>', 'circle', $this->getSession());
        $this
            ->object($circle->center)
            ->isInstanceOf('PommProject\Foundation\Converter\Type\Point')
            ->float($circle->center->x)
            ->isEqualTo(1.2345)
            ->float($circle->radius)
            ->isEqualTo(3.141596)
            ;
    }

    public function testToPg()
    {
        $circle = $this->newTestedInstance()->fromPg('<(1.2345,-9.87654),3.141596>', 'circle', $this->getSession());
        $this
            ->string($this->newTestedInstance()->toPg($circle, 'circle', $this->getSession()))
            ->isEqualTo('circle(point(1.2345,-9.87654),3.141596)')
            ->string($this->newTestedInstance()->toPg('<(1.2345,-9.87654),3.141596>', 'circle', $this->getSession()))
            ->isEqualTo('circle(point(1.2345,-9.87654),3.141596)')
            ->string($this->newTestedInstance()->toPg(null, 'mycircle', $this->getSession()))
            ->isEqualTo('NULL::mycircle')
            ->exception(function() {
                return $this->newTestedInstance()->toPg('azsdf', 'circle', $this->getSession());
            })
            ->isInstanceOf('\PommProject\Foundation\Exception\ConverterException')
            ;
    }
}
