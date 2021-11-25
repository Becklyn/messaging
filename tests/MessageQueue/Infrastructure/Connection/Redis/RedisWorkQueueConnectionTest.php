<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis;

use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\RedisWorkQueueConnection;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\RedisWorkQueueConnection
 */
class RedisWorkQueueConnectionTest extends TestCase
{
    use ProphecyTrait;

    private ClientInterface|ObjectProphecy $predis;

    private RedisWorkQueueConnection $fixture;

    protected function setUp() : void
    {
        $this->predis = $this->prophesize(ClientInterface::class);
        $this->fixture = new RedisWorkQueueConnection($this->predis->reveal());
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

    public function testReadMessageWithMessageInQueueDoesReturnMessageString() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        $expectedSerializedMessage = "test-message";
        $this->predis->brpop(["message-queue:{$queue->name()}"], Argument::any())
            ->willReturn(["", $expectedSerializedMessage])
            ->shouldBeCalledOnce();

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertEquals($expectedSerializedMessage, $serializedMessage);
    }

    public function testReadMessageWithNoMessageInQueueDoesReturnNull() : void
    {
        // Arrange
        $queue = new QueueDefinition("test");

        $this->predis->brpop(["message-queue:{$queue->name()}"], Argument::any())
            ->willReturn(null)
            ->shouldBeCalledOnce();

        // Act
        $serializedMessage = $this->fixture->readMessage($queue);

        // Assert
        self::assertNull($serializedMessage);
    }

    public function testPublishMessageDoesCallLPushWithValidArguments() : void
    {
        // Arrange
        $destination = new Destination("test");
        $serializedMessage = "test-message";

        $this->predis->lpush("message-queue:{$destination->destination()}", [$serializedMessage])
            ->shouldBeCalledOnce();

        // Act
        $this->fixture->publishMessage($destination, $serializedMessage);

        // Assert
        // Only prophecy assertions ...
    }
}
