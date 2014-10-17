<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Query;

use PommProject\Foundation\Query\ListenerInterface;

/**
 * ListenerAwareInterface
 *
 * Interface for Query instance to notify listeners about queries.
 *
 * @package Pomm
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface ListenerAwareInterface
{
    /**
     * registerListener
     *
     * Register a listener for notifications.
     *
     * @access public
     * @param  Listener $listener
     * @return null
     */
    public function registerListener(ListenerInterface $listener);
}

