<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Consumer\Worker\Symfony;

use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\InvalidMessageException;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\UnsupportedMessageException;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\Worker;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-09-16
 */
abstract class SymfonySerializedNativeObjectMessageWorker implements Worker
{
    protected const EXPECTED_TYPE = '';

    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(Message $message) : void
    {
        try {
            $content = $this->serializer->deserialize(
                $message->content()->serializedContent(),
                $message->content()->contentType(),
                $message->content()->serializationFormat()
            );
        } catch (\Throwable $e) {
            throw new InvalidMessageException('Message could not be deserialized.', $e);
        }

        $contentType = \is_object($content) ? $content::class : \gettype($content);

        if (static::EXPECTED_TYPE && $contentType !== static::EXPECTED_TYPE) {
            throw new UnsupportedMessageException("Message content is of type {$contentType} but " . static::EXPECTED_TYPE . " is expected. Serialized content:\r\n{$message->content()->serializedContent()}");
        }

        $this->process($content);
    }

    /**
     * @param mixed $content Message content to be processed by the worker
     */
    abstract protected function process(mixed $content) : void;
}
