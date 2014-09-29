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

use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\DatabaseConfiguration;
use PommProject\Foundation\Session;
use Atoum;

abstract class BaseConverter extends Atoum
{
    protected $session;

    protected function getDatabaseConfiguration()
    {
        return new DatabaseConfiguration($GLOBALS['pomm_db1']);
    }

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = new Session($this->getDatabaseConfiguration());
            $this->session->registerClientPooler(new ConverterPooler());
        }

        return $this->session;
    }
}
