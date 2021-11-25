<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer;

use Becklyn\Messaging\MessageQueue\Application\Consumer\ConsumerParams;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\ConsumerParams
 */
class ConsumerParamsTest extends TestCase
{
    public function testGettersReturnValuesPassedToConstructor() : void
    {
        // Arrange
        $timeout = \random_int(1, 10000);
        $mtl = \random_int(1, 1000);

        // Act
        $fixture = new ConsumerParams($timeout, $mtl);

        // Assert
        self::assertEquals($timeout, $fixture->timeout());
        self::assertEquals($mtl, $fixture->mtl());
    }
}
