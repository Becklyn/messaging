<?php declare(strict_types=1);

namespace Becklyn\Messaging\Tests\MessageQueue\Infrastructure\Consumer\Symfony;

use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleHookResult;
use Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Symfony\SymfonyLifecycleManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-25
 *
 * @covers \Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Symfony\SymfonyLifecycleManager
 */
class SymfonyLifecycleManagerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|EntityManagerInterface $em;

    private SymfonyLifecycleManager $fixture;

    protected function setUp() : void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->fixture = new SymfonyLifecycleManager($this->em->reveal());
    }

    public function testPreHookWillReturnSuccessfulLifecycleHookResultIfEntityManagerIsOpen() : void
    {
        // Arrange
        $this->em->isOpen()->willReturn(true);

        // Act
        $lifecycleHookResult = $this->fixture->preHook();

        // Assert
        Assert::assertEquals(LifecycleHookResult::successful(), $lifecycleHookResult);
    }

    public function testPreHookWillReturnFailedLifecycleHookResultIfEntityManagerIsNotOpen() : void
    {
        // Arrange
        $this->em->isOpen()->willReturn(false);

        // Act
        $lifecycleHookResult = $this->fixture->preHook();

        // Assert
        Assert::assertFalse($lifecycleHookResult->isSuccessful());
    }
}
