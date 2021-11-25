<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer\Worker;

/**
 * Should be thrown by workers when message (not its content) is invalid.
 *
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class InvalidMessageException extends \Exception
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        if ($previous) {
            $previousClass = $previous::class;
            $message = "{$message}\r\nException message:\r\n{$previous->getMessage()}\r\nException class:\r\n{$previousClass}\r\nStack trace:\r\n{$previous->getTraceAsString()}";
        }
        parent::__construct($message, 0, $previous);
    }
}
