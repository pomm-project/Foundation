<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit;

use PommProject\Foundation\SessionBuilder;
use PommProject\Foundation\Session;
use Atoum;

abstract class SessionAwareAtoum extends Atoum
{
    private $session_builder;

    protected function getSession()
    {
        $session = $this->getSessionBuilder()->buildSession();
        $this->initializeSession($session);

        return $session;
    }

    protected function getSessionBuilder()
    {
        if ($this->session_builder === null) {
            $this->session_builder = $this->createSessionBuilder($GLOBALS['pomm_db1']);
        }

        return $this->session_builder;
    }

    protected function createSessionBuilder($configuration)
    {
        return new SessionBuilder($configuration);
    }

    abstract protected function initializeSession(Session $session);
}
