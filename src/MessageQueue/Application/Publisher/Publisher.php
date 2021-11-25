<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Publisher;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Publishers push messages into abstract queues. Concrete message queue implementations are responsible for delivering messages to their
 * destinations.
 *
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class Publisher
{
    public function __construct(
        private Connection $connection,
        private SerializerInterface $serializer,
    ) {}

    /**
     * @throws PublisherException
     */
    public function publish(Message $message) : void
    {
        try {
            $serializedMessage = $this->serializer->serialize($message, 'json');
        } catch (\Throwable $e) {
            throw new PublisherException('Message could not be serialized.', $e);
        }

        try {
            $this->connection->publishMessage($message->destination(), $serializedMessage);
        } catch (\Throwable $e) {
            throw new PublisherException('Message could not be pushed into the queue.', $e);
        }
    }
}
