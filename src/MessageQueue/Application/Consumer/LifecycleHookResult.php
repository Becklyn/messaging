<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Consumer;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-05
 */
class LifecycleHookResult
{
    public static function successful() : self
    {
        return new self(true, null);
    }

    public static function failed(string $errorMessage) : self
    {
       return new self(false, $errorMessage);
    }

    private function __construct(
        private bool $successful,
        private ?string $errorMessage,
    ) {}

    public function isSuccessful() : bool
    {
        return $this->successful;
    }

    public function errorMessage() : ?string
    {
        return $this->errorMessage;
    }
}
