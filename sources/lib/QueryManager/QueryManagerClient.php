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

use PommProject\Foundation\Client\Client;

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
abstract class QueryManagerClient extends Client implements QueryManagerInterface
{
    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType(): string
    {
        return 'query_manager';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier(): string
    {
        return $this::class;
    }
}
