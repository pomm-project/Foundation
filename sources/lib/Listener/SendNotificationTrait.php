<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Listener;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Session\Session;

/**
 * SendNotificationTrait
 *
 * Trait to add sendNotification method to clients.
 *
 * @package     Foundation
 * @copyright   2014 - 2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 */
trait SendNotificationTrait
{
    /**
     * getSession
     *
     * sendNotification needs to access the session.
     */
    abstract protected function getSession(): Session;

    /**
     * sendNotification
     *
     * Send notification to the listener pooler.
     *
     * @access protected
     * @param string $name
     * @param array $data
     * @return mixed
     * @throws FoundationException
     */
    protected function sendNotification(string $name, array $data): mixed
    {
        /** @var Listener $listener */
        $listener = $this
            ->getSession()
            ->getPoolerForType('listener');

        $listener->notify($name, $data);

        return $this;
    }
}
