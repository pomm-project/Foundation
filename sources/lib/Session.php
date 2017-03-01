<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Session\Session as VanillaSession;

/**
 * Session
 *
 * Session with Foundation poolers API.
 *
 * @package     Foundation
 * @copyright   2014 - 2017 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see         VanillaSession
 */
class Session extends VanillaSession
{
    /**
     * getPreparedQuery
     *
     * Return the prepared query client.
     *
     * @param   string $query
     * @return  \PommProject\Foundation\PreparedQuery\PreparedQuery
     */
    public function getPreparedQuery($query)
    {
        return $this->getClientUsingPooler('prepared_query', $query);
    }

    /**
     * getQueryManager
     *
     * Return a query manager (default to QueryManager\SimpleQueryManager)
     *
     * @param   string              $query_manager
     * @return  \PommProject\Foundation\QueryManager\QueryManagerClient
     */
    public function getQueryManager($query_manager = null)
    {
        return $this->getClientUsingPooler('query_manager', $query_manager);
    }

    /**
     * getConverter
     *
     * Return a converter client.
     *
     * @param   string          $name
     * @return  \PommProject\Foundation\Converter\ConverterClient
     */
    public function getConverter($name)
    {
        return $this->getClientUsingPooler('converter', $name);
    }

    /**
     * getObserver
     *
     * Return an observer client.
     *
     * @param   string      $name
     * @return  \PommProject\Foundation\Observer\Observer
     */
    public function getObserver($name)
    {
        return $this->getClientUsingPooler('observer', $name);
    }

    /**
     * getInspector
     *
     * Return the database inspector.
     *
     * @param   string $name (null)
     * @return  \PommProject\Foundation\Inspector\Inspector
     */
    public function getInspector($name = null)
    {
        return $this->getClientUsingPooler('inspector', $name);
    }

    /**
     * getListener
     *
     * A short description here
     *
     * @param   string $name
     * @return  \PommProject\Foundation\Listener\Listener
     */
    public function getListener($name)
    {
        return $this->getClientUsingPooler('listener', $name);
    }
}
