<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\Session;
use PommProject\Foundation\Client\ClientPooler;

/**
 * PreparedQueryPooler
 *
 * Clients pooler for PreparedQuery instances.
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class PreparedQueryPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'prepared_statement';
    }

    /**
     * getPoolerType
     *
     * @access public
     * @param  string  SQL query
     * @return PreparedQuery
     * @see    ClientPoolerInterface
     */
    public function getClient($sql)
    {
        $query = $this->session->getClient(
            $this->getPoolerType(),
            PreparedQuery::getSignatureFor($sql)
        );

        if ($query === null) {
            $query = new PreparedQuery($sql);
            $this->session->registerClient($query);
        }

        return $query;
    }
}
