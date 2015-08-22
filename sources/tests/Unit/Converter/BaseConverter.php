<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Tester\FoundationSessionAtoum;
use PommProject\Foundation\Session\Session;

abstract class BaseConverter extends FoundationSessionAtoum
{
    protected function initializeSession(Session $session)
    {
    }

    /**
     * doesTypeExist
     *
     * Return true if the given type exists.
     *
     * @access protected
     * @param  string   $type
     * @return bool
     */
    protected function doesTypeExist($type, Session $session)
    {
        return ($session->getInspector()->getTypeInformation($type, 'public') !== null);
    }

    /**
     * isPgVersionAtLeast
     *
     * Return true if the server version meets client expectations.
     *
     * @access public
     * @param  string   $version
     * @param  Session  $session
     * @return bool
     */
    public function isPgVersionAtLeast($version, Session $session)
    {
        $current_version = $session->getInspector()->getVersion();

        return (bool) (version_compare($version, $current_version) <= 0);
    }

    /**
     * sendToPostgres
     *
     * To test toPgStandardFormat, values can be sent to Postgres and
     * retreived.
     *
     * @access protected
     * @param  mixed $value
     * @param  mixed $type
     * @param  Session $session
     * @return mixed query result
     */
    protected function sendToPostgres($value, $type, Session $session)
    {
        $result = $session
            ->getQueryManager()
            ->query(
                sprintf("select $*::%s as my_test", $type),
                [$value]
            )
            ->current()
            ;

        return $result['my_test'];
    }
}
