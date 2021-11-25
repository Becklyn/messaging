<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Message;

use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Message\Destination
 */
class DestinationTest extends TestCase
{
    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $queueName = \uniqid();
        $fixture = new Destination($queueName);
        self::assertEquals($queueName, $fixture->destination());
    }
}
