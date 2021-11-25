<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Connection;

use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-08
 */
interface Connection
{
    public function connect(QueueDefinition $queue) : void;

    public function disconnect(QueueDefinition $queue) : void;

    public function readMessage(QueueDefinition $queue, int $blockSeconds = \PHP_INT_MAX) : ?string;

    public function publishMessage(Destination $destination, string $serializedMessage) : void;
}
