<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer\Worker;

use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\InvalidMessageException;
use phpDocumentor\Reflection\DocBlock\Tags\Covers;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\InvalidMessageException
 */
class InvalidMessageExceptionTest extends TestCase
{
    public function testNewInvalidMessageExceptionWithPreviousThrowableChainsMessage() : void
    {
        // Arrange
        $previous = new \Exception(\uniqid());
        $previousClass = $previous::class;

        $message = \uniqid();
        $expectedMessage = "{$message}\r\nException message:\r\n{$previous->getMessage()}\r\nException class:\r\n{$previousClass}\r\nStack trace:\r\n{$previous->getTraceAsString()}";

        // Act
        $invalidMessageException = new InvalidMessageException($message, $previous);

        // Assert
        self::assertEquals($expectedMessage, $invalidMessageException->getMessage());
    }
}
