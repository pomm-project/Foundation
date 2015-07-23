<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PommProject\Foundation\Test\Unit\Tester;

use PommProject\Foundation\SessionBuilder;

/**
 * FoundationSessionAtoum
 *
 * Provide a Atoum with a session configured with Foundation default poolers.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @abstract
 * @see VanillaSessionAtoum
 */
abstract class FoundationSessionAtoum extends VanillaSessionAtoum
{
    /**
     * createSessionBuilder
     *
     * Override VanillaSessionAtoum to return a Foundation Session builder.
     *
     * @access  protected
     * @param   array $configuration
     * @return  SessionBuilder
     */
    protected function createSessionBuilder(array $configuration)
    {
        return new SessionBuilder($configuration);
    }
}
