<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
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
        $result = $session->getQueryManager()->query('show server_version', [])->current();

        return (bool) (version_compare($version, $result['server_version']) <= 0);
    }
}
