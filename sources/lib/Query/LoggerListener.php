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

use PommProject\Foundation\Query\ListenerInterface;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class LoggerListener implements LoggerAwareInterface, ListenerInterface
{
    use LoggerAwareTrait;

    public function notify($event, array $data)
    {
        if ($event === 'pre') {
            $message = $data['sql'];
            $context = $data['parameters'];
        } else if ($event === 'post') {
            $message = "Query ok.";
            $context = $data;
        } else {
            throw new FoundationException(
                sprintf(
                    "Do not know what to log for event '%s' (data {%s}).",
                    $event,
                    join(', ', array_map(function($val) { return sprintf("'%s'", $val); }))
                )
            );
        }

        $this->logger->info($message, $context);
    }
}
