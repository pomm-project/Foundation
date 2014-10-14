<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Exception;

/**
 * NotificationException
 *
 * Notification exception.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see FoundationException
 */
class NotificationException extends FoundationException
{
    protected $channel;
    protected $pid;

    /**
     * __construct
     *
     * Exception constructor.
     *
     * @access public
     * @param  array $notification
     * @return void
     */
    public function __construct(array $notification)
    {
        $this->channel = $notification['message'];
        $this->pid     = $notification['pid'];
        $this->message = $notification['payload'];
    }

    /**
     * getChannel
     *
     * Return the channel's name.
     *
     * @access public
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * getPid
     *
     * Return the server's PID.
     *
     * @access public
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }
}
