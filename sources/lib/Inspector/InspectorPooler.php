<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Exception\FoundationException;

/**
 * InspectorPooler
 *
 * Pooler for Inspector client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class InspectorPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'inspector';
    }

    /**
     * getClient
     *
     * @see     ClientPooler
     * @return Inspector
     */
    public function getClient($identifier = null)
    {
        if ($identifier === null) {
            $identifier = '\PommProject\Foundation\Inspector\Inspector';
        }

        return parent::getClient($identifier);
    }

    /**
     * createClient
     *
     * @see    ClientPooler
     * @return Inspector
     * @throws FoundationException
     */
    protected function createClient($identifier)
    {
        try {
            new \ReflectionClass($identifier);
        } catch (\ReflectionException $e) {
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
