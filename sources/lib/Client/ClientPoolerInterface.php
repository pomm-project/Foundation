<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Client;

use PommProject\Foundation\Session\Session;

/**
 * ClientPoolerInterface
 *
 * This interface make your pooler able to deal with Session's clients
 * holder.
 *
 * @package   Foundation
 * @copyright 2014 - 2017 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface ClientPoolerInterface
{
    /**
     * getType
     *
     * Return the client type which this pooler is to be associated with.
     *
     * @return string
     */
    public function getPoolerType();

    /**
     * register
     *
     * When Session registers a pooler, it injects itself so one can use
     * the ClientHolder when retrieving clients. It MUST return $this. The best
     * way not to care about this is to extends
     * \PommProject\Foundation\Client\ClientPooler.
     *
     * @param  Session               $session
     * @return ClientPoolerInterface $this
     */
    public function register(Session $session);

    /**
     * getClient
     *
     * Retrieve a client from session's ClientHolder.
     *
     * @param  string          $name
     * @return ClientInterface
     */
    public function getClient($name);
}
