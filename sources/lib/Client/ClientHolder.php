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

use PommProject\Foundation\Exception\PommException;

/**
 * ClientHolder
 *
 * Session clients are stored in this holder.
 *
 * @package   Pomm
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ClientHolder
{
    protected array $clients = [];

    /**
     * add
     *
     * Add a new client or replace existing one.
     *
     * @access public
     * @param  ClientInterface $client
     * @return ClientHolder    $this
     */
    public function add(ClientInterface $client): ClientHolder
    {
        $this->clients[$client->getClientType()][$client->getClientIdentifier()] = $client;

        return $this;
    }

    /**
     * has
     *
     * Tell if a client is in the pool or not.
     *
     * @access public
     * @param  string $type
     * @param  string $name
     * @return bool
     */
    public function has(string $type, string $name): bool
    {
        return isset($this->clients[$type][$name]);
    }

    /**
     * get
     *
     * Return a client by its name or null if no client exist for that name.
     *
     * @access public
     * @param string|null $type
     * @param string $name
     * @return ClientInterface|null
     */
    public function get(?string $type, string $name): ?ClientInterface
    {
        return $this->clients[$type][$name] ?? null;
    }

    /**
     * getAllFor
     *
     * Return all clients for a given type.
     *
     * @access public
     * @param  string $type
     * @return array
     */
    public function getAllFor(string $type): array
    {
        if (!isset($this->clients[$type])) {
            return [];
        }

        return $this->clients[$type];
    }

    /**
     * clear
     *
     * Call shutdown and remove a client from the pool. If the client does not
     * exist, nothing is done.
     *
     * @access public
     * @param  string       $type
     * @param  string       $name
     * @return ClientHolder $this
     */
    public function clear(string $type, string $name): ClientHolder
    {
        if (isset($this->clients[$type][$name])) {
            $this->clients[$type][$name]->shutdown();
            unset($this->clients[$type][$name]);
        }

        return $this;
    }

    /**
     * shutdown
     *
     * Call shutdown for all registered clients and unset the clients so they
     * can be cleaned by GC. It would have been better by far to use a
     * RecursiveArrayIterator to do this but it is not possible in PHP using
     * built'in iterators hence the double foreach recursion.
     * see http://fr2.php.net/manual/en/class.recursivearrayiterator.php#106519
     *
     * @access public
     * @return array exceptions caught during the shutdown
     */
    public function shutdown(): array
    {
        $exceptions = [];

        foreach ($this->clients as $type => $names) {
            foreach ($names as $name => $client) {
                try {
                    $client->shutdown();
                } catch (PommException $e) {
                    $exceptions[] = $e;
                }
            }
        }

        $this->clients = [];

        return $exceptions;
    }
}
