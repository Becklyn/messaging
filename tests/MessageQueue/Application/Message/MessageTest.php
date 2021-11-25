<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Message;

use Becklyn\Messaging\MessageQueue\Application\Message\Content;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Message\Message
 */
class MessageTest extends TestCase
{
    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $content = new Content('foo', 'bar', 'baz');
        $routeDefinition = new Destination('foo');
        $createdTs = new \DateTimeImmutable();
        $fixture = new Message($content, $routeDefinition, $createdTs);
        self::assertSame($content, $fixture->content());
        self::assertSame($routeDefinition, $fixture->destination());
        self::assertSame($createdTs, $fixture->createdTs());
    }
}
