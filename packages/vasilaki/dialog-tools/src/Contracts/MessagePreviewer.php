<?php

declare(strict_types=1);

namespace Vasilaki\DialogTools\Contracts;

interface MessagePreviewer
{
    /**
     * Build a short, word-aware preview of a message text.
     *
     * The text is trimmed to at most $limit characters without breaking the
     * last word and gets an ellipsis suffix when it was truncated.
     */
    public function preview(string $text, ?int $limit = null): string;

    /**
     * Build a URL/anchor friendly slug for a message text.
     */
    public function slug(string $text): string;
}
