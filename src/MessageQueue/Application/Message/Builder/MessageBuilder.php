<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Message\Builder;

use Becklyn\Messaging\MessageQueue\Application\Message\Message;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
interface MessageBuilder
{
    /**
     * @param mixed  $messageContent Content of the message, preferably an event, command or DTO
     * @param string $destination    Destination where the message should be routed to, represented as a string
     */
    public function build(mixed $messageContent, string $destination) : Message;
}
