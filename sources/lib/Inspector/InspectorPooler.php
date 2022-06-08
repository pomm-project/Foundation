<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Exception\FoundationException;

/**
 * InspectorPooler
 *
 * Pooler for Inspector client.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientPooler
 */
class InspectorPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType(): string
    {
        return 'inspector';
    }

    /**
     * getClient
     *
     * @param null|string $identifier
     * @return ClientInterface
     * @throws FoundationException
     * @see     ClientPooler
     */
    public function getClient(?string $identifier = null): ClientInterface
    {
        if ($identifier === null) {
            $identifier = Inspector::class;
        }

        return parent::getClient($identifier);
    }

    /**
     * createClient
     *
     * @param string $identifier
     * @return Client
     * @throws FoundationException
     * @see    ClientPooler
     */
    protected function createClient(string $identifier): Client
    {
        try {
            new \ReflectionClass($identifier);
        } catch (\ReflectionException) {
            throw new FoundationException(
                sprintf(
                    "Unable to load inspector '%s'.",
                    $identifier
                )
            );
        }

        return new $identifier();
    }
}
