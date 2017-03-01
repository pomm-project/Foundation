<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Listener;

/**
 * SendNotificationTrait
 *
 * Trait to add sendNotification method to clients.
 *
 * @package     Foundation
 * @copyright   2014 - 2017 Grégoire HUBERT
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
    abstract protected function getSession();

    /**
     * sendNotification
     *
     * Send notification to the listener pooler.
     *
     * @param  string $name
     * @param  array $data
     * @return mixed $this
     */
    protected function sendNotification($name, array $data)
    {
        $this
            ->getSession()
            ->getPoolerForType('listener')
            ->notify($name, $data)
            ;

        return $this;
    }
}
