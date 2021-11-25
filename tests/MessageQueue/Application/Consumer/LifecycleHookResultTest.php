<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer;

use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleHookResult;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleHookResult
 */
class LifecycleHookResultTest extends TestCase
{
    public function testIsSuccessfulReturnsTrueOnSuccessfulResult() : void
    {
        // Arrange
        $lifecycleHookResult = LifecycleHookResult::successful();

        // Act
        $isSuccessful = $lifecycleHookResult->isSuccessful();

        // Assert
        self::assertTrue($isSuccessful);
    }

    public function testIsSuccessfulReturnsFalseOnFailedResult() : void
    {
        // Arrange
        $lifecycleHookResult = LifecycleHookResult::failed(\uniqid());

        // Act
        $isSuccessful = $lifecycleHookResult->isSuccessful();

        // Assert
        self::assertFalse($isSuccessful);
    }

    public function testErrorMessageReturnsNullOnSuccessfulResult() : void
    {
        // Arrange
        $lifecycleHookResult = LifecycleHookResult::successful();

        // Act
        $errorMessage = $lifecycleHookResult->errorMessage();

        // Assert
        self::assertNull($errorMessage);
    }

    public function testErrorMessageReturnsErrorMessageOnFailedResult() : void
    {
        // Arrange
        $expectedErrorMessage = \uniqid();
        $lifecycleHookResult = LifecycleHookResult::failed($expectedErrorMessage);

        // Act
        $errorMessage = $lifecycleHookResult->errorMessage();

        // Assert
        self::assertEquals($expectedErrorMessage, $errorMessage);
    }
}
