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

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\QueryManager\QueryManagerInterface;
use PommProject\Foundation\QueryParameterExpander;
use PommProject\Foundation\ResultIterator;
use PommProject\Foundation\Session;

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

        return $this;
    }

    /**
     * @see QueryManagerInterface
     */
    public function query($sql, $values = [])
    {
        if ($this->session === null) {
            throw new FoundationException(sprintf("Query manager is not initialized !"));
        }

        $resource = $this->session->getConnection()->sendQueryWithParameters(
            QueryParameterExpander::order($sql),
            $values
        );

        return new ResultIterator(
            $resource,
            $this->session->getDatabaseConfiguration()->getConverterHolder()
        );
    }
}
