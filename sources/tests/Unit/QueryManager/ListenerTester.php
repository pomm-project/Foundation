<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\QueryManager;

use PommProject\Foundation\Session\Session;

class ListenerTester
{
    public $is_called = false;
    public $sql;
    public $parameters = [];
    public $session_stamp;
    public $result_count;
    public $time_ms;

    public function call($event, array $data, Session $session)
    {
        $this->is_called = true;

        if (isset($data['sql'])) {
            $this->sql = $data['sql'];
        }
        if (isset($data['parameters'])) {
            $this->parameters = $data['parameters'];
        }
        if (isset($data['session_stamp'])) {
            $this->session_stamp = $data['session_stamp'];
        }
        if (isset($data['result_count'])) {
            $this->result_count = $data['result_count'];
        }
        if (isset($data['time_ms'])) {
            $this->time_ms = $data['time_ms'];
        }
    }

    public function clear()
    {
        $this->is_called = false;
        $this->sql = null;
        $this->parameters = null;
        $this->session_stamp = null;
        $this->session_stamp = null;
        $this->time_ms = null;
    }
}
