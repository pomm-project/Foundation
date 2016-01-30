<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Inspector;

use PommProject\Foundation\Where;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

/**
 * @engine isolate
 */
class SchemaInspector extends FoundationSessionAtoum
{
    use InspectorTestTrait;

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector('schema');
    }

    public function testGetSchemas()
    {
        $i = $this->getInspector();
        $this
            ->array(
                $i->getSchemas(Where::create("n.nspname = $*", ['inspector_test']))
                ->current()
            )
            ->hasKeys(['name', 'oid', 'comment', 'relations', 'owner'])
            ->integer(
                $i->getSchemas(Where::create("n.nspname = $*", ['whatever']))
                    ->count()
            )
            ->isEqualTo(0)
            ;
    }
}
