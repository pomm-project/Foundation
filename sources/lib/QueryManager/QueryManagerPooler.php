<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Exception\FoundationException;

/**
 * QueryManagerPooler
 *
 * Pooler for the query_manager clients type.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientPooler
 */
class QueryManagerPooler extends ClientPooler
{
    protected array $listeners = [];
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType(): string
    {
        return 'query_manager';
    }

    /**
     * getClientFromPool
     *
     * @param string $identifier
     * @return ClientInterface|null
     * @throws FoundationException
     * @see    ClientPooler
     */
    protected function getClientFromPool(string $identifier): ?ClientInterface
    {
        return $this
            ->getSession()
            ->getClient($this->getPoolerType(), trim($identifier, "\\"))
            ;
    }

    /**
     * createClient
     *
     * @param string $identifier
     * @return ClientInterface
     * @throws FoundationException
     * @see    ClientPooler
     */
    protected function createClient(string $identifier): ClientInterface
    {
        try {
            new \ReflectionClass($identifier);
        } catch (\ReflectionException $e) {
            throw new FoundationException(sprintf("Could not load class '%s'.", $identifier), 0, $e);
        }

        return new $identifier();
    }

    /**
     * getPoolerType
     *
     * @param null|string $identifier
     * @return Client
     * @throws FoundationException
     * @see ClientPooler
     */
    public function getClient($identifier = null): Client
    {
        if ($identifier === null) {
            $identifier = SimpleQueryManager::class;
        }

        /** @var Client $client */
        $client = parent::getClient($identifier);

        return $client;
    }
}
