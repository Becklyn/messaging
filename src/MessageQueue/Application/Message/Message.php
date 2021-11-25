<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Message;

/**
 * Message content should be use-case specific, ideally event or command objects. When properly routed, messages should
 * ultimately be processed by Workers which know how to adequately process their content. In this way, message queues
 * are effectively asynchronous, distributed event dispatchers or command buses.
 *
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class Message
{
    /**
     * @internal Should only be instantiated through MessageBuilder
     */
    public function __construct(
        private Content $content,
        private Destination $destination,
        private \DateTimeImmutable $createdTs,
    ) {}

    public function content() : Content
    {
        return $this->content;
    }

    public function destination() : Destination
    {
        return $this->destination;
    }

    public function createdTs() : \DateTimeImmutable
    {
        return $this->createdTs;
    }
}
