<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer\Worker;

use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\UnsupportedMessageException;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\UnsupportedMessageException
 */
class UnsupportedMessageExceptionTest extends TestCase
{
    public function testNewUnsupportedMessageExceptionWithPreviousThrowableChainsMessage() : void
    {
        // Arrange
        $previous = new \Exception(\uniqid());
        $previousClass = $previous::class;

        $message = \uniqid();
        $expectedMessage = "{$message}\r\nException message:\r\n{$previous->getMessage()}\r\nException class:\r\n{$previousClass}\r\nStack trace:\r\n{$previous->getTraceAsString()}";

        // Act
        $unsupportedMessageException = new UnsupportedMessageException($message, $previous);

        // Assert
        self::assertEquals($expectedMessage, $unsupportedMessageException->getMessage());
    }
}
