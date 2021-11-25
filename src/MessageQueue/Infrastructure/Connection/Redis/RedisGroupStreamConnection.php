<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadGroupCommand;
use Predis\ClientInterface;
use Predis\Command\Command;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-19
 */
class RedisGroupStreamConnection implements Connection
{
    public function __construct(
        private ClientInterface $redis,
    ) {
        $profile = $this->redis->getProfile();

        $profile->defineCommand("XREADGROUP", XreadGroupCommand::class); // @phpstan-ignore-line
        $profile->defineCommand("XADD", XaddCommand::class); // @phpstan-ignore-line
    }

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
        $groupConnectionString = \explode(";", $queue->name());
        $streamName = $groupConnectionString[0];
        $groupName = $groupConnectionString[1];
        $consumerName = $groupConnectionString[2];

        if ($blockSeconds * 1000 < \PHP_INT_MAX) {
            $blockMilliseconds = $blockSeconds * 1000;
        }
        $blockTime = $blockMilliseconds ?? $blockSeconds;

        $xreadGroupCommand = $this->redis->createCommand(
            "XREADGROUP",
            Command::normalizeArguments(["GROUP", "{$groupName}", "{$consumerName}", "BLOCK", "{$blockTime}", "COUNT", "1", "NOACK", "STREAMS", "{$streamName}", ">"]),
        );
        $this->redis->getConnection()->writeRequest($xreadGroupCommand);

        $streams = $this->redis->getConnection()->read(); // @phpstan-ignore-line

        if (null === $streams) {
            return null;
        }
        $stream = $streams[0];

        $serializedMessages = $stream[1]; // [0] -> Redis stream key
        $serializedMessage = $serializedMessages[0][1]; // [0] -> Redis event id

        return $serializedMessage[1];
    }

    public function publishMessage(Destination $destination, string $serializedMessage) : void
    {
        $xaddCommand = $this->redis->createCommand(
            "XADD",
            Command::normalizeArguments(["{$destination->destination()}", "*", "message", "{$serializedMessage}"]),
        );
        $this->redis->getConnection()->writeRequest($xaddCommand);
    }
}
