<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Client\Client;

/**
 * DatabaseInspector
 *
 * Global inspector, it will give informations about the following:
 *
 * * configuration settings
 * * version
 *
 * @package     Pomm
 * @copyright   2017 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 * @see Client
 */
class DatabaseInspector extends Client
{

    use InspectorTrait;

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'inspector';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return 'database';
    }

    /**
     * getVersion
     *
     * Return server version.
     *
     * @return  string
     */
    public function getVersion()
    {
        $row = $this
            ->executeSql("show server_version")
            ->current()
            ;

        return $row['server_version'];
    }
}
