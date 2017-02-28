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

use PommProject\Foundation\Inflector;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Client\ClientInterface;
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
    public function getPoolerType()
    {
        return 'inspector';
    }

    /**
     * getClient
     *
     * @see    ClientPooler
     * @param  null|string $identifier
     * @return Inspector
     */
    public function getClient($identifier = null)
    {
        if ($identifier === null) {
            $identifier = 'legacy';
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
        if (!class_exists($identifier)) {
            return $this->createBuiltinClient($identifier);
        }

        return $this->createCustomClient($identifier);
    }

    /**
     * createBuiltinClient
     *
     * Return an instance of a builtin inspector from its short name. Throws an
     * exception if the built in inspector does not exist.
     *
     * @param   string $identifier
     * @throws  FoundationException
     * @return  ClientInterface
     */
    private function createBuiltinClient($identifier)
    {
        $class_name = sprintf(
            "\PommProject\Foundation\Inspector\%sInspector",
            Inflector::studlyCaps($identifier)
        );

        if (!class_exists($class_name)) {
            throw new FoundationException(
                sprintf(
                    "Unknown built in inspector '%s'. Default inspector clients are {%s}.",
                    $identifier,
                    'database, schema, relation, type, role'
                )
            );
        }

        return new $class_name;
    }

    /**
     * createCustomClient
     *
     * Load a custom inspector client. Throws an exception if class cannot be
     * loaded or is not a ClientInterface.
     *
     * @param   string $identifier
     * @throws  FoundationException
     * @return  ClientInterface
     */
    private function createCustomClient($identifier)
    {
        try {
            $refl = new \ReflectionClass($identifier);

            if (!$refl->implementsInterface('\PommProject\Foundation\Client\ClientInterface')) {
                throw new FoundationException(
                    sprintf(
                        "Class '%s' must implement '\PommProject\Foundation\Client\ClientInterface'.",
                        $identifier
                    )
                );
            }
        } catch (\ReflectionException $e) {
            throw new FoundationException(
                sprintf(
                    "Cannot find inspector class '%s'.",
                    $identifier
                ),
                null,
                $e
            );
        }

        return new $identifier;
    }
}
