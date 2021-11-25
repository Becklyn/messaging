<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer;

use Becklyn\Messaging\MessageQueue\Application\Connection\Connection;
use Becklyn\Messaging\MessageQueue\Application\Consumer\Worker\Worker;
use Becklyn\Messaging\MessageQueue\Application\Message\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Consumers form loops listening to queues and receiving messages from them, passing them on to workers for processing.
 *
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class Consumer
{
    private const PING_PERIOD_SECONDS = 60;
    private const MAX_BLOCK_SECONDS = 30;

    public function __construct(
        private Connection $connection,
        private SerializerInterface $serializer,
        private LifecycleManager $lifecycleManager,
    ) {}

    public function consume(Worker $worker, QueueDefinition $queue, ConsumerParams $params) : void
    {
        $blockSeconds = $this->calculateBlockSeconds($params);
        $pingSeconds = $this->calculatePingTime($blockSeconds);

        $this->dumpSeparator($queue);
        $this->dump($queue, "Start consuming queue with timeout of {$params->timeout()} seconds, blocking period of {$blockSeconds} seconds, ping period of {$pingSeconds} seconds and MTL of {$params->mtl()}");

        $startTs = \time();
        $lastPingTs = $startTs;
        $messagesRead = 0;

        $this->connection->connect($queue);

        while (true) {
            $preHookResult = $this->lifecycleManager->preHook();

            if (!$preHookResult->isSuccessful()) {
                $this->dump($queue, $preHookResult->errorMessage());
                break;
            }

            $serializedMessage = $this->connection->readMessage($queue, $blockSeconds);

            if (null !== $serializedMessage) {
                $this->dump($queue, 'Got a message from queue, starting work');

                /** @var Message $message */
                $message = $this->serializer->deserialize($serializedMessage, Message::class, 'json');
                $worker->execute($message);

                $this->dump($queue, 'Work done on message from queue');

                ++$messagesRead;

                if ($params->mtl() && $messagesRead >= $params->mtl()) {
                    $this->dump($queue, "Consumer disconnecting after processing {$messagesRead} messages as defined by MTL");
                    break;
                }
            }

            $currTs = \time();

            $lifetime = $currTs - $startTs;

            if ($params->timeout() && $lifetime > $params->timeout()) {
                $this->dump($queue, "Consumer lifetime of {$lifetime} seconds exceeds timeout of {$params->timeout()} seconds");
                break;
            }

            if ($currTs >= $lastPingTs + $pingSeconds) {
                $this->dump($queue, "Pinging with I'm alive signal every {$pingSeconds} seconds!");
                $lastPingTs = $currTs;
            }
        }

        $this->connection->disconnect($queue);

        // supervisor treats processes that ended within a second as a fatal error (exited too soon), this is to avoid it
        if (\time() - $startTs <= 1) {
            \sleep(1);
        }

        $this->dump($queue, 'End consuming queue');
    }

    private function calculateBlockSeconds(ConsumerParams $params) : int
    {
        return $params->timeout() > self::MAX_BLOCK_SECONDS ? self::MAX_BLOCK_SECONDS : $params->timeout();
    }

    private function calculatePingTime(int $blockSeconds) : int
    {
        return $blockSeconds > self::PING_PERIOD_SECONDS ? $blockSeconds : self::PING_PERIOD_SECONDS;
    }

    private function dump(QueueDefinition $queue, string $message) : void
    {
        $time = new \DateTimeImmutable();
        \dump("[{$time->format('Y-m-d H:i:s')}] [RedisConsumer] [{$queue->name()}] {$message}");
    }

    private function dumpSeparator(QueueDefinition $queue) : void
    {
        \dump('-----------------------------------------------------------------------------');
    }
}
