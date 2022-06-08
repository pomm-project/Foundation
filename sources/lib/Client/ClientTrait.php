<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Client;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Exception\FoundationException;

/**
 * ClientTrait
 *
 * Abstract class for Session clients.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientInterface
 * @abstract
 */
trait ClientTrait
{
    private ?Session $session = null;

    /**
     * @see ClientInterface
     */
    public function initialize(Session $session): void
    {
        $this->session = $session;
    }

    /**
     * Most of the time, there is nothing to be done at shutdown.
     * @see ClientInterface
     */
    public function shutdown(): void
    {
    }

    /**
     * getSession
     *
     * All subclasses of Client have to use this method to access the session.
     *
     * @access protected
     * @throws  FoundationException if Session is not set.
     * @return Session
     */
    protected function getSession(): Session
    {
        if ($this->session === null) {
            throw new FoundationException(
                sprintf(
                    "Client '%s' is not initialized hence does not have a session.",
                    $this::class
                )
            );
        }

        return $this->session;
    }
}
