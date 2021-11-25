<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Consumer\Worker\Symfony;

use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\InvalidMessageException;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\UnsupportedMessageException;
use Becklyn\Messaging\MessageQueue\Application\Message\Content;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Worker\Symfony\SymfonySerializedNativeObjectMessageWorker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Worker\Symfony\SymfonySerializedNativeObjectMessageWorker
 */
class SymfonySerializedNativeObjectWorkerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|SerializerInterface $serializer;

    private ObjectProphecy|SymfonySerializedMessageWorkerTestDelegate $delegate;

    private SymfonySerializedMessageWorkerTestExpectingAnyProxy $expectingAnyFixture;

    private SymfonySerializedMessageWorkerTestExpectingArrayProxy $expectingPrimitiveFixture;

    private SymfonySerializedMessageWorkerTestExpectingObjectProxy $expectingObjectFixture;

    protected function setUp() : void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->delegate = $this->prophesize(SymfonySerializedMessageWorkerTestDelegate::class);

        $this->expectingAnyFixture = new SymfonySerializedMessageWorkerTestExpectingAnyProxy($this->serializer->reveal(), $this->delegate->reveal());
        $this->expectingPrimitiveFixture = new SymfonySerializedMessageWorkerTestExpectingArrayProxy(
            $this->serializer->reveal(),
            $this->delegate->reveal()
        );
        $this->expectingObjectFixture = new SymfonySerializedMessageWorkerTestExpectingObjectProxy($this->serializer->reveal(), $this->delegate->reveal());
    }

    public function testExecuteDeserializesMessageContentAndProcessesIt() : void
    {
        $deserializedContent = \uniqid();
        $serializedContent = \uniqid();
        $contentType = \uniqid();
        $serializationFormat = \uniqid();
        $message = new Message(new Content($serializedContent, $contentType, $serializationFormat), new Destination(\uniqid()), new \DateTimeImmutable());

        $this->serializer->deserialize($serializedContent, $contentType, $serializationFormat)->willReturn($deserializedContent);

        $this->expectingAnyFixture->execute($message);
        $this->delegate->execute($deserializedContent)->shouldHaveBeenCalledTimes(1);
    }

    public function testExecuteThrowsInvalidMessageExceptionIfDeserializationThrowsException() : void
    {
        $serializedContent = \uniqid();
        $contentType = \uniqid();
        $serializationFormat = \uniqid();
        $message = new Message(new Content($serializedContent, $contentType, $serializationFormat), new Destination(\uniqid()), new \DateTimeImmutable());

        $this->serializer->deserialize($serializedContent, $contentType, $serializationFormat)->willThrow(new \Exception());

        $this->expectException(InvalidMessageException::class);
        $this->expectingAnyFixture->execute($message);
    }

    public function testExecuteThrowsUnsupportedMessageExceptionIfMessageContentIsNotOfExpectedPrimitiveType() : void
    {
        $deserializedContent = 1;
        $serializedContent = \uniqid();
        $contentType = \uniqid();
        $serializationFormat = \uniqid();
        $message = new Message(new Content($serializedContent, $contentType, $serializationFormat), new Destination(\uniqid()), new \DateTimeImmutable());

        $this->serializer->deserialize($serializedContent, $contentType, $serializationFormat)->willReturn($deserializedContent);

        $this->expectException(UnsupportedMessageException::class);
        $this->expectingPrimitiveFixture->execute($message);
    }

    public function testExecuteThrowsUnsupportedMessageExceptionIfMessageContentIsNotOfExpectedClass() : void
    {
        $deserializedContent = new SymfonySerializedMessageWorkerTestExpectingObjectProxyUnexpectedObject();
        $serializedContent = \uniqid();
        $contentType = \uniqid();
        $serializationFormat = \uniqid();
        $message = new Message(new Content($serializedContent, $contentType, $serializationFormat), new Destination(\uniqid()), new \DateTimeImmutable());

        $this->serializer->deserialize($serializedContent, $contentType, $serializationFormat)->willReturn($deserializedContent);

        $this->expectException(UnsupportedMessageException::class);
        $this->expectingPrimitiveFixture->execute($message);
    }
}

abstract class SymfonySerializedMessageWorkerTestBaseProxy extends SymfonySerializedNativeObjectMessageWorker
{
    private SymfonySerializedMessageWorkerTestDelegate $delegate;

    public function __construct(SerializerInterface $serializer, SymfonySerializedMessageWorkerTestDelegate $delegate)
    {
        parent::__construct($serializer);
        $this->delegate = $delegate;
    }

    protected function process(mixed $content) : void
    {
        $this->delegate->execute($content);
    }
}

class SymfonySerializedMessageWorkerTestDelegate
{
    public function execute($arg) : void
    {
    }
}

class SymfonySerializedMessageWorkerTestExpectingArrayProxy extends SymfonySerializedMessageWorkerTestBaseProxy
{
    protected const EXPECTED_TYPE = 'array';
}

class SymfonySerializedMessageWorkerTestExpectingObjectProxy extends SymfonySerializedMessageWorkerTestBaseProxy
{
    protected const EXPECTED_TYPE = SymfonySerializedMessageWorkerTestExpectingObjectProxyExpectedObject::class;
}

class SymfonySerializedMessageWorkerTestExpectingObjectProxyExpectedObject
{
}

class SymfonySerializedMessageWorkerTestExpectingObjectProxyUnexpectedObject
{
}


class SymfonySerializedMessageWorkerTestExpectingAnyProxy extends SymfonySerializedMessageWorkerTestBaseProxy
{
    protected const EXPECTED_TYPE = '';
}
