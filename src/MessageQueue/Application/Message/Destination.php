<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Message;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class Destination
{
    public function __construct(
        private string $destination
    ) {}

    public function destination() : string
    {
        return $this->destination;
    }
}
