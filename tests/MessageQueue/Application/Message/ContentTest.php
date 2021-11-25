<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Message;

use Becklyn\Messaging\MessageQueue\Application\Message\Content;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Message\Content
 */
class ContentTest extends TestCase
{
    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $serializedContent = \uniqid();
        $contentType = \uniqid();
        $serializationFormat = \uniqid();
        $fixture = new Content($serializedContent, $contentType, $serializationFormat);
        self::assertEquals($serializedContent, $fixture->serializedContent());
        self::assertEquals($contentType, $fixture->contentType());
        self::assertEquals($serializationFormat, $fixture->serializationFormat());
    }
}
