<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\QueryManager\QueryManagerInterface;
use PommProject\Foundation\Session;
use PommProject\Foundation\QueryParameterExpander;

/**
 * SimpleQueryManager
 *
 * The simplest way to run parametrized queries.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 * @see QueryManagerInterface
 */
class SimpleQueryManager implements QueryManagerInterface
{
    protected $session;

    /**
     * @see QueryManagerInterface
     */
    public function initialize(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @see QueryManagerInterface
     */
    public function query($sql, $values = [])
    {
        if (pg_send_query_params(
                $this->session->getHandler(),
                QueryParameterExpander::order($sql),
                $values
            ) === false) {
            throw new ConnectionException(sprintf("Error while sending query '%s'.", $sql));
        }

        return new ResultIterator(
            $this->session->getQueryResult($sql),
            $this->session->getDatabaseConfiguration()->getConverterHolder()
        );
    }
}
