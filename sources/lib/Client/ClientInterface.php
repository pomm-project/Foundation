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

/**
 * ClientInterface
 *
 * An interface for classes to be registered as Session clients.
 *
 * @package   Pomm
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface ClientInterface
{
    /**
     * initialize
     *
     * This makes clients able to get Session environment hence perform
     * queries. Also useful to perform sanity checks.
     *
     * @access public
     * @param  Session $session
     * @return void
     */
    public function initialize(Session $session): void;

    /**
     * shutdown
     *
     * Perform some computations when the instance is removed from the pool.
     * Most of the time, instances are removed from the pool before the
     * Session is closed, you may have things to clean before it happens.
     *
     * @access public
     * @return void
     */
    public function shutdown(): void;

    /**
     * getClientType
     *
     * Must return the type of the session client. You may have several
     * classes for each type of client.
     *
     * @access public
     * @return string
     */
    public function getClientType(): string;

    /**
     * getClientIdentifier
     *
     * Each client must have a unique identifier so it will be pooled in by the
     * Session
     *
     * @access public
     * @return string
     */
    public function getClientIdentifier(): string;
}
