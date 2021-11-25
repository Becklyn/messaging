<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Publisher;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Message\Content;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Becklyn\Messaging\MessageQueue\Application\Publisher\Publisher;
use Becklyn\Messaging\MessageQueue\Application\Publisher\PublisherException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Publisher\Publisher
 */
class PublisherTest extends TestCase
{
    use ProphecyTrait;

    private Connection|ObjectProphecy $connection;

    private SerializerInterface|ObjectProphecy $serializer;

    private Publisher $fixture;

    protected function setUp() : void
    {
        $this->connection = $this->prophesize(Connection::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->fixture = new Publisher($this->connection->reveal(), $this->serializer->reveal());
    }

    public function testPublishSerializedMessageAndPublishesItOnConnection() : void
    {
        // Arrange
        $destination = \uniqid();
        $message = new Message(
            new Content(\uniqid(), \uniqid(), \uniqid()),
            new Destination($destination),
            new \DateTimeImmutable(),
        );
        $serializedMessage = \uniqid();

        $this->serializer->serialize($message, 'json')
            ->willReturn($serializedMessage);

        // Act
        $this->fixture->publish($message);

        // Assert
        $this->connection->publishMessage($message->destination(), $serializedMessage)
            ->shouldHaveBeenCalledOnce();
    }

    public function testPublishThrowsPublisherExceptionOnSerializationExceptionAndDoesNotPublishMessageOnConnection() : void
    {
        // Arrange
        $message = new Message(new Content(\uniqid(), \uniqid(), \uniqid()), new Destination(\uniqid()), new \DateTimeImmutable());
        $serializedMessage = \uniqid();

        $this->serializer->serialize($message, 'json')->willThrow(new \Exception());

        // Act
        $this->expectException(PublisherException::class);
        $this->fixture->publish($message);

        // Assert
        $this->connection->publishMessage($message->destination(), $serializedMessage)
            ->shouldNotHaveBeenCalled();
    }

    public function testPublishThrowsPublisherExceptionIfPushingToRedisThrowsException() : void
    {
        // Arrange
        $destination = \uniqid();
        $message = new Message(new Content(\uniqid(), \uniqid(), \uniqid()), new Destination($destination), new \DateTimeImmutable());
        $serializedMessage = \uniqid();

        $this->serializer->serialize($message, 'json')
            ->willReturn($serializedMessage);

        $this->connection->publishMessage($message->destination(), $serializedMessage)
            ->willThrow(new \Exception())
            ->shouldBeCalledOnce();

        // Act
        $this->expectException(PublisherException::class);
        $this->fixture->publish($message);

        // Assert
        // Only prophecy assertions ...
    }
}
