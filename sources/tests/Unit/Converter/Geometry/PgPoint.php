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
use PommProject\Foundation\Converter\Type\Point;

class PgPoint extends BaseConverter
{
    public function testFromPg()
    {
        $this
            ->object($this->newTestedInstance()->fromPg('(1.2345,-9.87654)', 'point', $this->getSession()))
            ->isInstanceOf('PommProject\Foundation\Converter\Type\Point')
            ->isEqualTo(new Point(1.2345, -9.87654))
            ->variable($this->newTestedInstance()->fromPg(null, 'point', $this->getSession()))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $point = new Point(1.2345, -9.87654);
        $this
            ->string($this->newTestedInstance()->toPg($point, 'point', $this->getSession()))
            ->isEqualTo('point(1.2345,-9.87654)')
            ->string($this->newTestedInstance()->toPg('(1.2345,-9.87654)', 'point', $this->getSession()))
            ->isEqualTo('point(1.2345,-9.87654)')
            ->exception(function() {
                return $this->newTestedInstance()->toPg('azsdf', 'point', $this->getSession());
            })
            ->isInstanceOf('\PommProject\Foundation\Exception\ConverterException')
            ->string($this->newTestedInstance()->toPg(null, 'subpoint', $this->getSession()))
            ->isEqualTo('NULL::subpoint')
            ;
    }
}

