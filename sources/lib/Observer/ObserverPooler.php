<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Observer;

use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Client\ClientPooler;
use PommProject\Foundation\Observer\Observer;

/**
 * ObserverPooler
 *
 * Pooler for observer clients.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPooler
 */
class ObserverPooler extends ClientPooler
{
    /*
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'observer';
    }

    /**
     * getClient
     *
     * Pool an Observer instance.
     *
     * @access public
     * @param  string $channel
     * @return Observer
     * @see    ClientPoolerInterface
     */
    public function getClient($channel)
    {
        $observer = $this
            ->getSession()
            ->getClient($this->getPoolerType(), $channel)
            ;

        if ($observer === null) {
            $observer = new Observer($channel);
            $session->registerClient($observer);
        }

        return $observer;
    }
}
