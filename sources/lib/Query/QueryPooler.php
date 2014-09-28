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

use PommProject\Foundation\Session;
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
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getClient($client = null)
    {
        if ($client === null) {
            $client = '\PommProject\Foundation\Query\SimpleQuery';
        }

        if (!$this->session->hasClient($this->getPoolerType(), trim($client, "\\"))) {
            $this->session->registerClient(new $client());
        }

        return $this
            ->session
            ->getClient($this->getPoolerType(), trim($client, "\\"))
            ;
    }
}
