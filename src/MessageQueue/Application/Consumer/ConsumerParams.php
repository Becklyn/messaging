<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class ConsumerParams
{
    /**
     * @param int $timeout The consumer should terminate itself if this number of seconds is elapsed while waiting for a message from the queue. Set to 0 for
     *                     no timeout. 1 was chosen as default to avoid unintended infinite loops.
     * @param int $mtl     If greater than zero, the consumer should disconnect itself after processing this number of messages.
     */
    public function __construct(
        private int $timeout = 1,
        private int $mtl = 0,
    ) {}

    public function timeout() : int
    {
        return $this->timeout;
    }

    public function mtl() : int
    {
        return $this->mtl;
    }
}
