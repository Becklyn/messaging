<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Application\Consumer;

use Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Application\Consumer\QueueDefinition
 */
class QueueDefinitionTest extends TestCase
{
    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $name = \uniqid();
        $fixture = new QueueDefinition($name);
        self::assertEquals($name, $fixture->name());
    }
}
