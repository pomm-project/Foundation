<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Fixture;

use PommProject\Foundation\Query\ListenerInterface;

class QueryListener implements ListenerInterface
{
    protected $events = [];
    protected $event_data = [];
    protected $counter = 0;

    public function notify($event, array $data)
    {
        $this->counter++;
        $this->events[]     = $event;
        $this->event_data[] = $data;
    }

    public function getLastEventType()
    {
        return array_pop($this->events);
    }

    public function getLastEventData()
    {
        return array_pop($this->event_data);
    }

    public function getCounter()
    {
        return $this->counter;
    }
}
