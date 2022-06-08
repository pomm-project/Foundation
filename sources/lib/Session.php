<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation;

use PommProject\Foundation\Converter\ConverterClient;
use PommProject\Foundation\Inspector\Inspector;
use PommProject\Foundation\Listener\Listener;
use PommProject\Foundation\Observer\Observer;
use PommProject\Foundation\PreparedQuery\PreparedQuery;
use PommProject\Foundation\QueryManager\QueryManagerClient;
use PommProject\Foundation\Session\Session as VanillaSession;

/**
 * Session
 *
 * Session with Foundation poolers API.
 *
 * @package     Foundation
 * @copyright   2014 - 2015 Grégoire HUBERT
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
     * @access  public
     * @param string $query
     * @return  PreparedQuery
     * @throws Exception\FoundationException
     */
    public function getPreparedQuery(string $query): PreparedQuery
    {
        /** @var PreparedQuery $preparedQuery */
        $preparedQuery = $this->getClientUsingPooler('prepared_query', $query);
        return $preparedQuery;
    }

    /**
     * getQueryManager
     *
     * Return a query manager (default to QueryManager\SimpleQueryManager)
     *
     * @access  public
     * @param string|null $query_manager
     * @return  QueryManagerClient
     * @throws Exception\FoundationException
     */
    public function getQueryManager(?string $query_manager = null): QueryManagerClient
    {
        /** @var QueryManagerClient $queryManagerClient */
        $queryManagerClient = $this->getClientUsingPooler('query_manager', $query_manager);
        return $queryManagerClient;
    }

    /**
     * getConverter
     *
     * Return a converter client.
     *
     * @access  public
     * @param string $name
     * @return  ConverterClient
     * @throws Exception\FoundationException
     */
    public function getConverter(string $name): ConverterClient
    {
        /** @var ConverterClient $converterClient */
        $converterClient = $this->getClientUsingPooler('converter', $name);
        return $converterClient;
    }

    /**
     * getObserver
     *
     * Return an observer client.
     *
     * @access  public
     * @param string $name
     * @return  Observer
     * @throws Exception\FoundationException
     */
    public function getObserver(string $name): Observer
    {
        /** @var Observer $observer */
        $observer = $this->getClientUsingPooler('observer', $name);
        return $observer;
    }

    /**
     * getInspector
     *
     * Return the database inspector.
     *
     * @access  public
     * @param string|null $name (null)
     * @return Inspector
     * @throws Exception\FoundationException
     */
    public function getInspector(?string $name = null): Inspector
    {
        /** @var Inspector $inspector */
        $inspector = $this->getClientUsingPooler('inspector', $name);
        return $inspector;
    }

    /**
     * getListener
     *
     * A short description here
     *
     * @access  public
     * @param string $name
     * @return  Listener
     * @throws Exception\FoundationException
     */
    public function getListener(string $name): Listener
    {
        /** @var Listener $listener */
        $listener = $this->getClientUsingPooler('listener', $name);
        return $listener;
    }
}
