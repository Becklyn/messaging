<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Application\Message;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2019-07-25
 */
class Content
{
    public function __construct(
        private string $serializedContent,
        private string $contentType,
        private string $serializationFormat,
    ) {}

    public function serializedContent() : string
    {
        return $this->serializedContent;
    }

    public function contentType() : string
    {
        return $this->contentType;
    }

    public function serializationFormat() : string
    {
        return $this->serializationFormat;
    }
}
