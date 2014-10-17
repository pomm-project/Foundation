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

interface ListenerInterface
{
    /**
     * notify
     *
     * Receive a notification from the query pooler. 
     *
     * @access public
     * @param  string $event
     * @param  array  $data
     * @return null
     */
    public function notify($event, array $data);
}
