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
            ->boolean(version_compare($result, "9.1.0") === 1)
            ->isTrue()
           ;
    }
}
