<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Listener\SendNotificationTrait;
use PommProject\Foundation\Session\ResultHandler;
use PommProject\Foundation\Converter\ConverterClient;

/**
 * SimpleQueryManager
 *
 * Query system as a client.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class SimpleQueryManager extends QueryManagerClient
{
    use SendNotificationTrait;
    use QueryParameterParserTrait;

    /**
     * query
     *
     * Perform a simple escaped query and return converted result iterator.
     *
     * @access public
     * @param string $sql
     * @param array $parameters
     * @return ConvertedResultIterator
     * @throws FoundationException
     */
    public function query(string $sql, array $parameters = []): ConvertedResultIterator
    {
        $parameters = $this->prepareArguments($sql, $parameters);
        $this->sendNotification(
            'query:pre',
            [
                'sql'           => $sql,
                'parameters'    => $parameters,
                'session_stamp' => $this->getSession()->getStamp(),
            ]
        );
        $start    = microtime(true);
        $resource = $this->doQuery($sql, $parameters);
        $end      = microtime(true);

        $iterator = new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );
        $this->sendNotification(
            'query:post',
            [
                'result_count' => $iterator->count(),
                'time_ms'      => sprintf("%03.1f", ($end - $start) * 1000),
            ]
        );

        return $iterator;
    }

    /**
     * doQuery
     *
     * Perform the query
     *
     * @access protected
     * @param string $sql
     * @param array $parameters
     * @return ResultHandler
     * @throws FoundationException
     */
    protected function doQuery(string $sql, array $parameters): ResultHandler
    {
        return $this
            ->getSession()
            ->getConnection()
            ->sendQueryWithParameters(
                $this->orderParameters($sql),
                $parameters
            )
            ;
    }

    /**
     * prepareArguments
     *
     * Prepare and convert $parameters if needed.
     *
     * @access protected
     * @param string $sql
     * @param array $parameters
     * @return array    $parameters
     * @throws FoundationException
     */
    protected function prepareArguments(string $sql, array $parameters): array
    {
        $types = $this->getParametersType($sql);

        foreach ($parameters as $index => $value) {
            if ($types[$index] !== '') {
                /** @var ConverterClient $converterClient */
                $converterClient = $this
                    ->getSession()
                    ->getClientUsingPooler('converter', $types[$index]);

                $parameters[$index] = $converterClient->toPgStandardFormat($value, $types[$index]);
            }
        }

        return $parameters;
    }
}
