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
}
