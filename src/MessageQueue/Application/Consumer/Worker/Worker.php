<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer\Worker;

use Becklyn\Messaging\MessageQueue\Application\Message\Message;

/**
 * Workers handle messages received from queues.
 *
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
interface Worker
{
    /**
     * @throws InvalidMessageException
     * @throws UnsupportedMessageException
     */
    public function execute(Message $message) : void;
}
