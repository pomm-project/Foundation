<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Exception\FoundationException;

/**
 * QueryManagerPooler
 *
 * Pooler for the query_manager clients type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class QueryManagerPooler extends ClientPooler
{
    protected $listeners = [];
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'query_manager';
    }

    /**
     * getClientFromPool
     *
     * @see    ClientPooler
     * @return ClientInterface
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
     * @return ClientInterface
     */
    protected function createClient($client)
    {
        try {
            new \ReflectionClass($client);

        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Could not load class '%s'.", $client), null, $e);
        }

        $client_instance = new $client();

        return $client_instance;
    }

    /**
     * getPoolerType
     *
     * @see ClientPooler
     */
    public function getClient($client = null)
    {
        if ($client === null) {
            $client = '\PommProject\Foundation\QueryManager\SimpleQueryManager';
        }

        return parent::getClient($client);
    }
}
