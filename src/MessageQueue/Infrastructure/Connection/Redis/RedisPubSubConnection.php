<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Predis\ClientInterface;
use Predis\Command\Command;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-11
 */
class RedisPubSubConnection implements Connection
{
    public function __construct(
        private ClientInterface $redis,
    ) {}

    public function connect(QueueDefinition $queue) : void
    {
        $subscribeCommand = $this->redis->createCommand(
            "SUBSCRIBE",
            Command::normalizeArguments([$queue->name()]),
        );
        $this->redis->getConnection()->writeRequest($subscribeCommand);
    }

    public function disconnect(QueueDefinition $queue) : void
    {
        $subscribeCommand = $this->redis->createCommand(
            "UNSUBSCRIBE",
            Command::normalizeArguments([$queue->name()]),
        );
        $this->redis->getConnection()->writeRequest($subscribeCommand);
    }

    public function readMessage(QueueDefinition $queue, int $blockSeconds = \PHP_INT_MAX) : ?string
    {
        $serializedMessage = $this->redis->getConnection()->read(); // @phpstan-ignore-line

        if ("message" !== $serializedMessage[0]) {
            return null;
        }
        return $serializedMessage[2];
    }

    public function publishMessage(Destination $destination, string $serializedMessage) : void
    {
        $this->redis->publish($destination->destination(), $serializedMessage);
    }
}
