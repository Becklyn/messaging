<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Message\Builder\Symfony;

use Becklyn\Messaging\MessageQueue\Application\Message\Builder\MessageBuilder;
use Becklyn\Messaging\MessageQueue\Application\Message\Content;
use Becklyn\Messaging\MessageQueue\Application\Message\Destination;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-09-16
 */
class SymfonyJsonSerializedMessageBuilder implements MessageBuilder
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    /**
     * @inheritDoc
     */
    public function build(mixed $messageContent, string $destination) : Message
    {
        return new Message(
            new Content(
                $this->serializer->serialize($messageContent, 'json'),
                \is_object($messageContent) ? $messageContent::class : \gettype($messageContent),
                'json'
            ),
            new Destination($destination),
            new \DateTimeImmutable()
        );
    }
}
