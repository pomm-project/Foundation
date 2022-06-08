<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Exception\FoundationException;

/**
 * PreparedQueryPooler
 *
 * Clients pooler for PreparedQuery instances.
 *
 * @package   Pomm
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientPooler
 */
class PreparedQueryPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType(): string
    {
        return 'prepared_query';
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
            ->getClient(
                $this->getPoolerType(),
                PreparedQuery::getSignatureFor($identifier)
            )
        ;
    }

    /**
     * createClient
     *
     * @param string $identifier SQL query
     * @return PreparedQuery
     * @throws FoundationException
     * @see    ClientPooler
     */
    public function createClient(string $identifier): PreparedQuery
    {
        return new PreparedQuery($identifier);
    }
}
