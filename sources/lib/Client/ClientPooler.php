<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Client;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Session;

/**
 * ClientPooler
 *
 * Base class for client poolers. ClientPooler instances are factories for
 * Session's Client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPoolerInterface
 * @abstract
 */
abstract class ClientPooler implements ClientPoolerInterface
{
    private $session;

    /**
     * register
     *
     * @see ClientPoolerInterface
     */
    public function register(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * getSession
     *
     * Check if the session is set and return it.
     *
     * @access protected
     * @return Session
     */
    protected function getSession()
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Client pooler '%s' is not initialized, session not set.", get_class($this)));
        }

        return $this->session;
    }
}

