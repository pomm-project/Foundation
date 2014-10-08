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

use PommProject\Foundation\DatabaseConfiguration;
use PommProject\Foundation\Session;
use Atoum;

abstract class SessionAwareAtoum extends Atoum
{
    private $session;

    protected function getDatabaseConfiguration()
    {
        return new DatabaseConfiguration($GLOBALS['pomm_db1']);
    }

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = new Session($this->getDatabaseConfiguration());
            $this->initializeSession($this->session);
        }

        return $this->session;
    }

    abstract protected function initializeSession(Session $session);
}
