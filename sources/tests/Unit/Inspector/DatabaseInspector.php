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

use PommProject\Foundation\Tester\FoundationSessionAtoum;

/**
 * @engine isolate
 */
class DatabaseInspector extends FoundationSessionAtoum
{
    use InspectorTestTrait;

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector('database');
    }

    public function testGetVersion()
    {
        $result = $this->getInspector()->getVersion();

        $this
            ->boolean(version_compare($result, "9.2.0") === 1)
            ->isTrue()
           ;
    }

    public function testGetSizePretty()
    {
        $this
            ->assert('Checking getSizePretty returns the size in human readable format.')
            ->given($result = $this->getInspector()->getSizePretty())
            ->string($result)
            ->matches('#[0-9]+\s(T|G|M|k)?B#')
           ;
    }

    public function testGetSize()
    {
        $this
            ->assert('Checking getSize returns an int.')
            ->given($result = $this->getInspector()->getSize())
            ->integer($result)
           ;
    }

    public function testGetName()
    {
        $this
            ->assert('Checking getName returns a string.')
            ->given($result = $this->getInspector()->getName())
            ->string($result)
           ;
    }
}
