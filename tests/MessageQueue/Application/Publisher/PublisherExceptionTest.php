<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Publisher;

use Becklyn\Messaging\MessageQueue\Application\Publisher\PublisherException;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Publisher\PublisherException
 */
class PublisherExceptionTest extends TestCase
{
    public function testNewPublisherExceptionWithPreviousThrowableChainesMessage() : void
    {
        // Arrange
        $previous = new \Exception(\uniqid());
        $previousClass = $previous::class;

        $message = \uniqid();
        $expectedMessage = "{$message}\r\nException message:\r\n{$previous->getMessage()}\r\nException class:\r\n{$previousClass}\r\nStack trace:\r\n{$previous->getTraceAsString()}";

        // Act
        $publisherException = new PublisherException($message, $previous);

        // Assert
        self::assertEquals($expectedMessage, $publisherException->getMessage());
    }
}
