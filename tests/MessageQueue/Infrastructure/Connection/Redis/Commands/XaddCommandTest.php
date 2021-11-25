<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands;

use Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands\XaddCommand
 */
class XaddCommandTest extends TestCase
{
    use ProphecyTrait;

    private XaddCommand $fixture;

    protected function setUp() : void
    {
        $this->fixture = new XaddCommand();
    }

    public function testGetIdReturnsXADD() : void
    {
        // Arrange
        $expectedCommandId = "XADD";

        // Act
        $commandId = $this->fixture->getId();

        // Assert
        self::assertEquals($expectedCommandId, $commandId);
    }
}
