<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Query;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Query\QueryInterface;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\QueryParameterExpander;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Session;

/**
 * SimpleQuery
 *
 * Query system as a client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 * @see ClientInterface
 */
class SimpleQuery extends Client implements QueryInterface
{
    /**
     * query
     *
     * @see QueryInterface
     */
    public function query($sql, array $parameters = [])
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Query client is not initialized !"));
        }

        $resource = $this
            ->session
            ->getConnection()
            ->sendQueryWithParameters(QueryParameterExpander::order($sql), $parameters)
            ;

        return new ConvertedResultIterator(
            $resource,
            $this->session
        );
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'query';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return get_class($this);
    }

    /**
     * shutdown
     *
     * @see ClientInterface
     */
    public function shutdown()
    {
    }
}
