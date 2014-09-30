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

use PommProject\Foundation\Test\Unit\SessionAwareAtoum;
use PommProject\Foundation\Converter\ConverterPooler;

class BaseConverter extends SessionAwareAtoum
{
    protected function registerClientPoolers()
    {
        parent::registerClientPoolers();
        $this->session->registerClientPooler(new ConverterPooler());
    }
}
