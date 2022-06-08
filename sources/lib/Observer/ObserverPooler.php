<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Observer;

use PommProject\Foundation\Client\ClientPoolerInterface;
use PommProject\Foundation\Client\ClientPooler;

/**
 * ObserverPooler
 *
 * Pooler for observer clients.
 *
 * @package   Foundation
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ClientPooler
 */
class ObserverPooler extends ClientPooler
{
    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType(): string
    {
        return 'observer';
    }

    /**
     * createClient
     *
     * @param  string   $identifier
     * @return Observer
     *@see    ClientPooler
     */
    protected function createClient(string $identifier): Observer
    {
        return new Observer($identifier);
    }
}
