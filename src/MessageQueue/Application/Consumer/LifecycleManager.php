<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-05
 */
interface LifecycleManager
{
    public function preHook() : LifecycleHookResult;
}
