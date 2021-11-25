<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Consumer;
use Becklyn\Messaging\MessageQueue\Application\Consumer\ConsumerParams;
use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleHookResult;
use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleManager;
use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\Worker;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\Consumer
 */
class ConsumerTest extends TestCase
{
    use ProphecyTrait;

    private Connection|ObjectProphecy $connection;

    private SerializerInterface|ObjectProphecy $serializer;

    private LifecycleManager|ObjectProphecy $lifecycleManager;

    private Consumer $fixture;

    protected function setUp() : void
    {
        $this->connection = $this->prophesize(Connection::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->lifecycleManager = $this->prophesize(LifecycleManager::class);

        $this->fixture = new Consumer(
            $this->connection->reveal(),
            $this->serializer->reveal(),
            $this->lifecycleManager->reveal(),
        );
    }

    public function testConsumeWillEvaluateLifecyclePreHook() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams(1);

        $this->connection->connect(Argument::any());
        $this->connection->disconnect(Argument::any());

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::successful())
            ->shouldBeCalledOnce();

        $this->connection->readMessage(Argument::any(), Argument::any())
            ->will(function () use ($params) : ?string {
                // Without the timeout and sleep the worker would never terminate
                \sleep($params->timeout() + 1);
                return null;
            });

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        // Only prophecy assertions ...
    }

    public function testConsumeWillBreakImmediatelyIfLifecyclePreHookEvaluationFails() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams();

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::failed(\uniqid()));

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        $this->connection->readMessage(Argument::any(), Argument::any())
            ->shouldNotHaveBeenCalled();
    }

    public function testConsumeWillEndWithoutCallingWorkerIfConnectionReturnsNull() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams(1);

        $this->connection->connect(Argument::any());
        $this->connection->disconnect(Argument::any());

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::successful());

        $this->connection->readMessage($queue, Argument::any())
            ->will(function () use ($params) : ?string {
                // Without the timeout and sleep the worker would never terminate
                \sleep($params->timeout() + 1);
                return null;
            });

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        $worker->execute(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testConsumeWillDeserializeMessageAndPassItToWorkerIfRedisReturnsKeyValueArray() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams(mtl: 1);

        $serializedMessage = \uniqid();
        /** @var Message $message */
        $message = $this->prophesize(Message::class)->reveal();

        $this->connection->connect(Argument::any());
        $this->connection->disconnect(Argument::any());

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::successful());

        $connection = $this->connection;
        $this->connection->readMessage($queue, Argument::any())
            ->will(function () use ($connection, $queue, $serializedMessage, $params) : ?string {
                $connection->readMessage($queue, Argument::any())->willReturn(null);
                return $serializedMessage;
            });

        $this->serializer->deserialize($serializedMessage, Message::class, 'json')
            ->willReturn($message);

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        $worker->execute($message)->shouldHaveBeenCalledOnce();
    }

    public function testConsumeWillBreakAfterProcessedMessageReachesMTL() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams(mtl: 10);

        $serializedMessage = \uniqid();
        /** @var Message $message */
        $message = $this->prophesize(Message::class)->reveal();

        $this->connection->connect(Argument::any());
        $this->connection->disconnect(Argument::any());

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::successful());

        $this->connection->readMessage($queue, Argument::any())
            ->willReturn($serializedMessage);

        $this->serializer->deserialize($serializedMessage, Message::class, 'json')
            ->willReturn($message);

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        $worker->execute($message)->shouldHaveBeenCalled($params->mtl());
    }

    public function testConsumeWillBreakAfterRuntimeReachesTimeout() : void
    {
        // Arrange
        /** @var Worker|ObjectProphecy $worker */
        $worker = $this->prophesize(Worker::class);
        $queue = new QueueDefinition("test");
        $params = new ConsumerParams(1);

        $serializedMessage = \uniqid();
        /** @var Message $message */
        $message = $this->prophesize(Message::class)->reveal();

        $this->connection->connect(Argument::any());
        $this->connection->disconnect(Argument::any());

        $this->lifecycleManager->preHook()
            ->willReturn(LifecycleHookResult::successful());

        $this->connection->readMessage($queue, Argument::any())
            ->will(function () use ($serializedMessage, $params) : ?string {
                \sleep($params->timeout() + 1);
                return $serializedMessage;
            });

        $this->serializer->deserialize($serializedMessage, Message::class, 'json')
            ->willReturn($message);

        // Act
        $this->fixture->consume($worker->reveal(), $queue, $params);

        // Assert
        $worker->execute($message)->shouldHaveBeenCalledOnce();
    }
}
