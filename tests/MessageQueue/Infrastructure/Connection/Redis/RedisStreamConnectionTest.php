<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\RedisStreamConnection;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Predis\Command\Command;
use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Connection\NodeConnectionInterface;
use Predis\Profile\RedisProfile;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\RedisStreamConnection
 */
class RedisStreamConnectionTest extends TestCase
{
    use ProphecyTrait;

    private ClientInterface|ObjectProphecy $predis;

    private RedisStreamConnection $fixture;

    protected function setUp() : void
    {
        $this->predis = $this->prophesize(ClientInterface::class);

        /** @var RedisProfile|ObjectProphecy $redisProfile */
        $redisProfile = $this->prophesize(RedisProfile::class);
        $this->predis->getProfile()->willReturn($redisProfile->reveal());

        $this->fixture = new RedisStreamConnection($this->predis->reveal());
    }

    public function testNewRedisStreamConnectionDefinesXreadAndXaddCommands() : void
    {
        // Arrange

        /** @var RedisProfile|ObjectProphecy $redisProfile */
        $redisProfile = $this->prophesize(RedisProfile::class);

        $this->predis->getProfile()->willReturn($redisProfile->reveal());

        // Act
        new RedisStreamConnection($this->predis->reveal());

        // Assert
        $redisProfile->defineCommand("XREAD", XreadCommand::class)
            ->shouldHaveBeenCalledOnce();
        $redisProfile->defineCommand("XADD", XaddCommand::class)
            ->shouldHaveBeenCalledOnce();
    }

    public function testConnectDoesNothing() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        // Act
        $this->fixture->connect($queue);

        // Assert
        self::assertTrue(true);
    }

    public function testDisconnectDoesNothing() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        // Act
        $this->fixture->disconnect($queue);

        // Assert
        self::assertTrue(true);
    }

    public function testReadMessageWithMessageAvailableOnStreamReturnsMessageString() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");
        $expectedSerializedMessage = "test-message";

        /** @var Command|ObjectProphecy $xreadCommand */
        $xreadCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XREAD",
            Command::normalizeArguments(["BLOCK", \PHP_INT_MAX, "COUNT", "1", "STREAMS", "{$queue->name()}", "$"]),
        )->willReturn($xreadCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->writeRequest($xreadCommand)
            ->shouldBeCalledOnce();
        $predisConnection->read()
            ->willReturn([["", [["", ["", $expectedSerializedMessage]]]]])
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertEquals($expectedSerializedMessage, $serializedMessage);
    }

    public function testReadMessageWithNoMessageAvailableOnStreamReturnsNull() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        /** @var Command|ObjectProphecy $xreadCommand */
        $xreadCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XREAD",
            Command::normalizeArguments(["BLOCK", \PHP_INT_MAX, "COUNT", "1", "STREAMS", "{$queue->name()}", "$"]),
        )->willReturn($xreadCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->writeRequest($xreadCommand)
            ->shouldBeCalledOnce();
        $predisConnection->read()
            ->willReturn(null)
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertNull($serializedMessage);
    }

    public function testPublishMessageShouldCreateXaddCommandAndWriteRequest() : void
    {
        // Arrange
        $destination = new Destination("test");
        $serializedMessage = "test-message";

        /** @var CommandInterface|ObjectProphecy $xaddCommand */
        $xaddCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XADD",
            Command::normalizeArguments(["{$destination->destination()}", "*", "message", "{$serializedMessage}"]),
        )->willReturn($xaddCommand)->shouldBeCalledOnce();

        /** @var ConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(ConnectionInterface::class);
        $predisConnection->writeRequest($xaddCommand)
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $this->fixture->publishMessage($destination, $serializedMessage);

        // Assert
        // Only prophecy assertions ...
    }
}
