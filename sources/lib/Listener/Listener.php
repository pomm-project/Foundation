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
use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Client\Client;

/**
 * Listener
 *
 * Listener client.
 * This class may attach actions that are triggered by events.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       Client
 */
class Listener extends Client
{
    protected array $actions = [];

    /**
     * __construct
     *
     * Take the client identifier as argument.
     *
     * @access public
     * @param  string $name
     */
    public function __construct(protected string $name)
    {
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType(): string
    {
        return 'listener';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier(): string
    {
        return $this->name;
    }

    /**
     * attachAction
     *
     * Attach a new callback to the callback list.
     *
     * @access public
     * @param  callable $action
     * @return Listener $this
     */
    public function attachAction(callable $action): Listener
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * notify
     *
     * Trigger actions. All actions are executed passing the following parameters:
     * string   $name    name of the event
     * array    $data    event's payload if any
     * Session  $session the current session
     *
     * @access public
     * @param string $name
     * @param array $data
     * @return Listener $this
     * @throws FoundationException
     */
    public function notify(string $name, array $data): Listener
    {
        foreach ($this->actions as $action) {
            call_user_func($action, $name, $data, $this->getSession());
        }

        return $this;
    }
}
