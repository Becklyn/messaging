<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadCommand;
use Predis\ClientInterface;
use Predis\Command\Command;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-19
 */
class RedisStreamConnection implements Connection
{
    public function __construct(
        private ClientInterface $redis,
    ) {
        $profile = $this->redis->getProfile();

        $profile->defineCommand("XREAD", XreadCommand::class); // @phpstan-ignore-line
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
        $xreadCommand = $this->redis->createCommand(
            "XREAD",
            Command::normalizeArguments(["BLOCK", "{$blockSeconds}", "COUNT", "1", "STREAMS", "{$queue->name()}", "$"]),
        );
        $this->redis->getConnection()->writeRequest($xreadCommand);

        $streams = $this->redis->getConnection()->read(); // @phpstan-ignore-line

        if (null === $streams) {
            return null;
        }
        // No further null-check, since we always
        // get a well-defined array for this method!
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
