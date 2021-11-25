<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands;

use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadGroupCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadGroupCommand
 */
class XreadGroupCommandTest extends TestCase
{
    use ProphecyTrait;

    private XreadGroupCommand $fixture;

    protected function setUp() : void
    {
        $this->fixture = new XreadGroupCommand();
    }

    public function testGetIdReturnsXREADGROUP() : void
    {
        // Arrange
        $expectedCommandId = "XREADGROUP";

        // Act
        $commandId = $this->fixture->getId();

        // Assert
        self::assertEquals($expectedCommandId, $commandId);
    }
}
