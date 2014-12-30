<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PommProject\Foundation\Tester;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Session\SessionBuilder;

use Atoum;

/**
 * VanillaSessionAtoum
 *
 * This is a Session aware Atoum class. It uses the vanilla session builder
 * hence produce session with no poolers nor clients.
 * It is intended to be overloaded by each package to add their own poolers.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @abstract
 * @see Atoum
 */
abstract class VanillaSessionAtoum extends Atoum
{
    private $session_builder;

    /**
     * buildSession
     *
     * A short description here
     *
     * @access protected
     * @param  string       $stamp
     * @return Session
     */
    protected function buildSession($stamp = null)
    {
        $session = $this->getSessionBuilder()->buildSession($stamp);
        $this->initializeSession($session);

        return $session;
    }

    /**
     * getSessionBuilder
     *
     * Return a SessionBuilder.
     *
     * @access protected
     * @return SessionBuilder
     */
    private function getSessionBuilder()
    {
        if ($this->session_builder === null) {
            $this->session_builder = $this->createSessionBuilder($GLOBALS['pomm_db1']);
        }

        return $this->session_builder;
    }

    /**
     * createSessionBuilder
     *
     * Instanciate a new SessionBuilder. This method is to be overloaded by
     * each package to insstanciate their own SessionBuilder if any.
     *
     * @access protected
     * @param  array $configuration
     * @return SessionBuilder
     */
    protected function createSessionBuilder($configuration)
    {
        return new SessionBuilder($configuration);
    }

    /**
     * initializeSession
     *
     * If the test needs special poolers and/or client configuration, it goes
     * here.
     *
     * @access protected
     * @param Session $session
     * @return null
     */
    abstract protected function initializeSession(Session $session);
}
