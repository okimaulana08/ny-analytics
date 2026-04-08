<?php

namespace App\Exceptions;

use RuntimeException;

class NovelyaPublishException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $storyCreated = false,
        public readonly int $chaptersSent = 0,
    ) {
        parent::__construct($message);
    }
}
