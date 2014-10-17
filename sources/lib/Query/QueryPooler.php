<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Query;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Query\ListenerAwareInterface;
use PommProject\Foundation\Query\ListenerTrait;

class QueryPooler extends ClientPooler implements ListenerAwareInterface
{
    protected $listeners = [];
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'query';
    }

    /**
     * getClientFromPool
     *
     * @see    ClientPooler
     * @return Client
     */
    protected function getClientFromPool($client)
    {
        return $this
            ->getSession()
            ->getClient($this->getPoolerType(), trim($client, "\\"))
            ;
    }

    /**
     * createClient
     *
     * @see    ClientPooler
     * @param  string   $client_class_name
     * @throw  FoundationException
     * @return Client
     */
    protected function createClient($client)
    {
        try {
            $reflection = new \ReflectionClass($client);

            if (!$reflection->implementsInterface(
                '\PommProject\Foundation\Query\ListenerAwareInterface'
            )) {
                throw new FoundationException(
                    sprintf(
                        "Client '%s' does not implements ListenerAwareInterface.",
                        $client
                    )
                );
            }

        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Could not load class '%s'.", $client), null, $e);
        }

        $instance = new $client();

        foreach ($this->listeners as $listener) {
            $instance->registerListener($listener);
        }

        return $instance;
    }

    /**
     * getPoolerType
     *
     * @see ClientPooler
     */
    public function getClient($client = null)
    {
        if ($client === null) {
            $client = '\PommProject\Foundation\Query\SimpleQuery';
        }

        return parent::getClient($client);
    }

    public function registerListener(ListenerInterface $listener)
    {
        foreach ($this->getSession()->getAllClientForType('query') as $query_client) {
            $query_client->registerListener($listener);
        }

        $this->listeners[] = $listener;
    }
}
