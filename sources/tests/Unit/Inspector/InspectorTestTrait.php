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

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Test\Fixture\InspectorFixture;
use PommProject\Foundation\Exception\FoundationException;

trait InspectorTestTrait
{
    protected $session;

    abstract protected function getInspector();

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = $this->buildSession();
        }

        return $this->session;
    }

    protected function initializeSession(Session $session)
    {
        $session
            ->registerClient(new InspectorFixture())
            ;
    }

    protected function getFixture()
    {
        $fixture = $this
            ->getSession()
            ->getClient('fixture', 'inspector')
            ;

        if ($fixture === null) {
            throw new FoundationException(
                "Unable to get client 'fixture'::'inspector' from the session's client pool.
                ");
        }

        return $fixture;
    }

    public function setUp()
    {
        $this->getFixture()->createSchema();
    }

    public function tearDown()
    {
        $this->getFixture()->dropSchema();
    }
}
