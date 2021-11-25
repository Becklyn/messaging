<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Message\Builder\Symfony;

use Becklyn\Messaging\MessageQueue\Infrastructure\Message\Builder\Symfony\SymfonyJsonSerializedMessageBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Infrastructure\Message\Builder\Symfony\SymfonyJsonSerializedMessageBuilder
 */
class SymfonyJsonSerializedMessageBuilderTest extends TestCase
{
    use ProphecyTrait;

    private SymfonyJsonSerializedMessageBuilder $fixture;

    private ObjectProphecy|SerializerInterface $serializer;

    protected function setUp() : void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->fixture = new SymfonyJsonSerializedMessageBuilder($this->serializer->reveal());
    }

    public function testBuildReturnsMessageWithSerializedContentReturnedFromSerializer() : void
    {
        $content = \uniqid();
        $serializedContent = \uniqid();
        $this->serializer->serialize($content, 'json')->willReturn($serializedContent);

        $message = $this->fixture->build($content, \uniqid());
        self::assertEquals($serializedContent, $message->content()->serializedContent());
    }

    public function primitiveContentProvider() : array
    {
        return [
            [['foo' => 'bar']],
            ['foo'],
            [1],
            [1.1],
            [null],
            [true],
        ];
    }

    /**
     * @dataProvider primitiveContentProvider
     */
    public function testBuildReturnsMessageWithProperTypeForPrimitives($content) : void
    {
        $this->serializer->serialize($content, 'json')->willReturn(\uniqid());

        $message = $this->fixture->build($content, \uniqid());
        self::assertEquals(\gettype($content), $message->content()->contentType());
    }

    public function testBuildReturnsMessageWithProperClassNameForObjects() : void
    {
        $content = new SymfonyJsonSerializedMessageBuilderTestObjectSerializationDummy(\uniqid());
        $this->serializer->serialize($content, 'json')->willReturn(\uniqid());

        $message = $this->fixture->build($content, \uniqid());
        self::assertEquals(\get_class($content), $message->content()->contentType());
    }

    public function testBuildReturnsMessageWithJsonAsSerialisationFormat() : void
    {
        $content = \uniqid();
        $this->serializer->serialize($content, 'json')->willReturn(\uniqid());

        $message = $this->fixture->build($content, \uniqid());
        self::assertEquals('json', $message->content()->serializationFormat());
    }

    public function testBuildReturnsMessageWithProperDestination() : void
    {
        $content = \uniqid();
        $destination = \uniqid();
        $this->serializer->serialize($content, 'json')->willReturn(\uniqid());

        $message = $this->fixture->build($content, $destination);
        self::assertEquals($destination, $message->destination()->destination());
    }

    public function testBuildReturnsMessageWithCreatedTs() : void
    {
        $content = \uniqid();
        $this->serializer->serialize($content, 'json')->willReturn(\uniqid());

        $message = $this->fixture->build($content, \uniqid());
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $message->createdTs());
    }
}

class SymfonyJsonSerializedMessageBuilderTestObjectSerializationDummy
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}
