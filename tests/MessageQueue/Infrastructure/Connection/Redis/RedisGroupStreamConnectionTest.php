<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadGroupCommand;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\RedisGroupStreamConnection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Predis\Command\Command;
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
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\RedisGroupStreamConnection
 */
class RedisGroupStreamConnectionTest extends TestCase
{
    use ProphecyTrait;

    private ClientInterface|ObjectProphecy $predis;

    private RedisGroupStreamConnection $fixture;

    protected function setUp() : void
    {
        $this->predis = $this->prophesize(ClientInterface::class);

        /** @var RedisProfile|ObjectProphecy $redisProfile */
        $redisProfile = $this->prophesize(RedisProfile::class);
        $this->predis->getProfile()->willReturn($redisProfile->reveal());

        $this->fixture = new RedisGroupStreamConnection($this->predis->reveal());
    }

    public function testNewRedisGroupStreamConnectionDefinesXreadGroupAndXaddCommands() : void
    {
        // Arrange

        /** @var RedisProfile|ObjectProphecy $redisProfile */
        $redisProfile = $this->prophesize(RedisProfile::class);

        $this->predis->getProfile()->willReturn($redisProfile->reveal());

        // Act
        new RedisGroupStreamConnection($this->predis->reveal());

        // Assert
        $redisProfile->defineCommand("XREADGROUP", XreadGroupCommand::class)
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
        $queue = new QueueDefinition("testStream;testGroup;testConsumer");
        $expectedSerializedMessage = "test-message";

        $groupConnectionString = \explode(";", $queue->name());
        $streamName = $groupConnectionString[0];
        $groupName = $groupConnectionString[1];
        $consumerName = $groupConnectionString[2];

        /** @var Command|ObjectProphecy $xreadGroupCommand */
        $xreadGroupCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XREADGROUP",
            Command::normalizeArguments(["GROUP", "{$groupName}", "{$consumerName}", "BLOCK", \PHP_INT_MAX, "COUNT", "1", "NOACK", "STREAMS", "{$streamName}", ">"]),
        )->willReturn($xreadGroupCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->writeRequest($xreadGroupCommand)
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

    public function testReadMessageWithBlockingSecondsBelowIntMaxCalculatesBlockingInMilliseconds() : void
    {
        // Arrange
        $queue = new QueueDefinition("testStream;testGroup;testConsumer");
        $blockingSeconds = 10;
        $expectedBlockingSeconds = $blockingSeconds * 1000;

        $groupConnectionString = \explode(";", $queue->name());
        $streamName = $groupConnectionString[0];
        $groupName = $groupConnectionString[1];
        $consumerName = $groupConnectionString[2];

        /** @var Command|ObjectProphecy $xreadGroupCommand */
        $xreadGroupCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XREADGROUP",
            Command::normalizeArguments(
                [
                    "GROUP", "{$groupName}", "{$consumerName}",
                    "BLOCK", "{$expectedBlockingSeconds}",
                    "COUNT", "1",
                    "NOACK", "STREAMS", "{$streamName}", ">", ]
            ),
        )->willReturn($xreadGroupCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->writeRequest($xreadGroupCommand)
            ->shouldBeCalledOnce();
        $predisConnection->read()
            ->willReturn(null)
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $this->fixture->readMessage($queue, $blockingSeconds);

        // Assert
        // Only prophecy assertions ...
    }

    public function testReadMessageWithNoMessageAvailableOnStreamReturnsNull() : void
    {
        // Arrange
        $queue = new QueueDefinition("testStream;testGroup;testConsumer");

        $groupConnectionString = \explode(";", $queue->name());
        $streamName = $groupConnectionString[0];
        $groupName = $groupConnectionString[1];
        $consumerName = $groupConnectionString[2];

        /** @var Command|ObjectProphecy $xreadGroupCommand */
        $xreadGroupCommand = $this->prophesize(Command::class);
        $this->predis->createCommand(
            "XREADGROUP",
            Command::normalizeArguments(
                [
                    "GROUP", "{$groupName}", "{$consumerName}",
                    "BLOCK", \PHP_INT_MAX,
                    "COUNT", "1",
                    "NOACK", "STREAMS", "{$streamName}", ">", ]
            ),
        )->willReturn($xreadGroupCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->writeRequest($xreadGroupCommand)
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
        $destination = new Destination("testStream;testGroup;testConsumer");
        $serializedMessage = "test-message";

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
