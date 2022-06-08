<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Fixture;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Session\Connection;
use PommProject\Foundation\Client\ClientHolder;
use PommProject\Foundation\Session\SessionBuilder;
use PommProject\Foundation\Listener\ListenerPooler;
use PommProject\Foundation\Test\Fixture\PommTestSession;

class PommTestSessionBuilder extends SessionBuilder
{
    /**
     * createSession
     *
     * Override default session.
     *
     * @see SessionBuilder
     * @return PommTestSession
     */
    protected function createSession(Connection $connection, ClientHolder $client_holder, ?string $stamp): Session
    {
        return new PommTestSession($connection, $client_holder, $stamp);
    }

    protected function postConfigure(Session $session): static
    {
        parent::postConfigure($session);

        $session
            ->registerClientPooler(new ListenerPooler)
            ;
        return $this;
    }
}
