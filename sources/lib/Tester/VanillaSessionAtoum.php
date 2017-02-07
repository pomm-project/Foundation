<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
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
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       Atoum
 * @abstract
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
    protected function buildSession($stamp = 'pomm_db1')
    {
        $session = $this->getSessionBuilder($stamp)->buildSession($stamp);
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
    private function getSessionBuilder($stamp)
    {
        if (!isset($this->session_builder[$stamp])) {
            $this->session_builder[$stamp] = $this->createSessionBuilder($GLOBALS[$stamp]);
        }

        return $this->session_builder[$stamp];
    }

    /**
     * createSessionBuilder
     *
     * Instantiate a new SessionBuilder. This method is to be overloaded by
     * each package to instantiate their own SessionBuilder if any.
     *
     * @access protected
     * @param  array $configuration
     * @return SessionBuilder
     */
    protected function createSessionBuilder(array $configuration)
    {
        return new SessionBuilder($configuration);
    }

    /**
     * initializeSession
     *
     * If the test needs special poolers and/or client configuration, it goes
     * here.
     *
     * @access  protected
     * @param   Session $session
     * @return  null
     */
    abstract protected function initializeSession(Session $session);
}
