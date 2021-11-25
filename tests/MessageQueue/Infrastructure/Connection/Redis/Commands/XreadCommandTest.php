<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands;

use Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Connection\Redis\Commands\XreadCommand
 */
class XreadCommandTest extends TestCase
{
    use ProphecyTrait;

    private XreadCommand $fixture;

    protected function setUp() : void
    {
        $this->fixture = new XreadCommand();
    }

    public function testGetIdReturnsXREAD() : void
    {
        // Arrange
        $expectedCommandId = "XREAD";

        // Act
        $commandId = $this->fixture->getId();

        // Assert
        self::assertEquals($expectedCommandId, $commandId);
    }
}
