<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Symfony;

use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleHookResult;
use Becklyn\Messaging\MessageQueue\Application\Consumer\LifecycleManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-05
 */
class SymfonyLifecycleManager implements LifecycleManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function preHook() : LifecycleHookResult
    {
        if (!$this->em->isOpen()) {
            return LifecycleHookResult::failed('Consumer disconnecting because the entity manager is closed');
        }
        return LifecycleHookResult::successful();
    }
}
