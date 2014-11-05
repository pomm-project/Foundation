<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\ResultHandler;
use PommProject\Foundation\QueryManager\SimpleQueryManager;

/**
 * PreparedQueryManager
 *
 * Query manager using the prepared_statement client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see SimpleQuery
 */
class PreparedQueryManager extends SimpleQueryManager
{
    /**
     * doQuery
     *
     * @see SimpleQuery
     */
    protected function doQuery($sql, array $parameters)
    {
        return $this
            ->getSession()
            ->getClientUsingPooler('prepared_query', $sql)
            ->execute($parameters)
            ;
    }
}
