<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class QueueDefinition
{
    public function __construct(
        private string $name,
    ) {}

    public function name() : string
    {
        return $this->name;
    }
}
