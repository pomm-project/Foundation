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

use PommProject\Foundation\Client\ClientPooler;

class QueryPooler extends ClientPooler
{
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
     * @return QueryInterface|null
     */
    protected function getClientFromPool($client)
    {
        return $this
            ->getSession()
            ->getClientFromPool($this->getPoolerType(), trim($client, "\\"))
            ;
    }

    /**
     * createClient
     *
     * @see    ClientPooler
     * @throw  FoundationException
     * @return QueryInterface
     */
    protected function createClient($client)
    {
        try {
            $reflection = new \ReflectionClass($client);

            if ($reflexion->implementsInterface('\PommProject\Foundation\Query\QueryInterface') === false) {
                throw new FoundationException(sprintf("Class '%s' is not a QueryInterface.", $client));
            }

        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Could not load class '%s'.", $client));
        }

        return new $client();
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
}
