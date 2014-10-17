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

/**
 * ListenerTrait
 *
 * Trait to implement ListenerAwareInterface.
 * It Additionally grant with a sendNotification() method that obviously sends
 * a notification to all listeners.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
trait ListenerTrait
{
    protected $listeners = [];

    /**
     * registerListener
     *
     * Add a new listener to the pooler.
     *
     * @access public
     * @param ListenerInterface $listener
     * @return SimpleQuery
     */
    public function registerListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * notify
     *
     * Send a notification to all listeners.
     *
     * @access protected
     * @param  string $event_type
     * @param  array $data
     * @return ListenerAwareInterface $this
     */
    protected function sendNotification($event_type, array $data)
    {
        foreach ($this->listeners as $listener) {
            $listener->notify($event_type, $data);
        }

        return $this;
    }
}
