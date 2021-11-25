<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Predis\ClientInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-11
 */
class RedisWorkQueueConnection implements Connection
{
    public function __construct(
        private ClientInterface $redis,
    ) {}

    public function connect(QueueDefinition $queue) : void
    {
        // NOP
    }

    public function disconnect(QueueDefinition $queue) : void
    {
        // NOP
    }

    public function readMessage(QueueDefinition $queue, int $blockSeconds = \PHP_INT_MAX) : ?string
    {
        $serializedMessage = $this->redis->brpop(["message-queue:{$queue->name()}"], $blockSeconds);

        if (null === $serializedMessage) {
            return null;
        }
        return $serializedMessage[1];
    }

    public function publishMessage(Destination $destination, string $serializedMessage) : void
    {
        $this->redis->lpush("message-queue:{$destination->destination()}", [$serializedMessage]);
    }
}
