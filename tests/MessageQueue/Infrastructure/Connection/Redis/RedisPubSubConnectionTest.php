<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\RedisPubSubConnection;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Predis\Command\Command;
use Predis\Connection\ConnectionInterface;
use Predis\Connection\NodeConnectionInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\RedisPubSubConnection
 */
class RedisPubSubConnectionTest extends TestCase
{
    use ProphecyTrait;

    private ClientInterface|ObjectProphecy $predis;

    private RedisPubSubConnection $fixture;

    protected function setUp() : void
    {
        $this->predis = $this->prophesize(ClientInterface::class);
        $this->fixture = new RedisPubSubConnection($this->predis->reveal());
    }

    public function testConnectShouldTriggerRedisSubscribeCommand() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        /** @var Command|ObjectProphecy $subscribeCommand */
        $subscribeCommand = $this->prophesize(Command::class);

        $this->predis->createCommand("SUBSCRIBE", Command::normalizeArguments([$queue->name()]))
            ->willReturn($subscribeCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var ConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(ConnectionInterface::class);
        $predisConnection->writeRequest($subscribeCommand)
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $this->fixture->connect($queue);

        // Assert
        // Only prophecy assertions ...
    }

    public function testDisconnectShouldTriggerRedisUnsubscribeCommand() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        /** @var Command|ObjectProphecy $unsubscribeCommand */
        $unsubscribeCommand = $this->prophesize(Command::class);

        $this->predis->createCommand("UNSUBSCRIBE", Command::normalizeArguments([$queue->name()]))
            ->willReturn($unsubscribeCommand->reveal())
            ->shouldBeCalledOnce();

        /** @var ConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(ConnectionInterface::class);
        $predisConnection->writeRequest($unsubscribeCommand)
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $this->fixture->disconnect($queue);

        // Assert
        // Only prophecy assertions ...
    }

    public function testReadMessageWithIncomingMessageShouldReturnMessageString() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");
        $expectedSerializedMessage = "test-message";

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->read()
            ->willReturn(["message", "", $expectedSerializedMessage])
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertEquals($expectedSerializedMessage, $serializedMessage);
    }

    public function testReadMessageWithNoIncomingMessageShouldReturnNull() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        /** @var NodeConnectionInterface|ObjectProphecy $predisConnection */
        $predisConnection = $this->prophesize(NodeConnectionInterface::class);
        $predisConnection->read()
            ->willReturn(["notmessage", "", ""])
            ->shouldBeCalledOnce();

        $this->predis->getConnection()->willReturn($predisConnection->reveal());

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertNull($serializedMessage);
    }

    public function testPublishMessageDoesCallPublishWithValidArguments() : void
    {
        // Arrange
        $destination = new Destination("test");
        $serializedMessage = "test-message";

        $this->predis->publish($destination->destination(), $serializedMessage)
            ->shouldBeCalledOnce();

        // Act
        $this->fixture->publishMessage($destination, $serializedMessage);

        // Assert
        // Only prophecy assertions ...
    }
}
