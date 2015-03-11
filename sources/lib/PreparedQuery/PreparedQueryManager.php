<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\ResultHandler;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\QueryManager\QueryManagerClient;

/**
 * PreparedQueryManager
 *
 * Query manager using the prepared_statement client.
 *
 * @package Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see SimpleQuery
 */
class PreparedQueryManager extends QueryManagerClient
{
    /**
     * query
     *
     * @see QueryManagerInterface
     */
    public function query($sql, array $parameters = [])
    {
        $resource = $this
            ->getSession()
            ->getClientUsingPooler('prepared_query', $sql)
            ->execute($parameters)
            ;

        return new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );

    }
}
