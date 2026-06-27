<?php

declare(strict_types=1);

namespace Vasilaki\DialogTools;

use Illuminate\Support\Str;
use Vasilaki\DialogTools\Contracts\MessagePreviewer;

class MessagePreviewerService implements MessagePreviewer
{
    public function __construct(
        private readonly int $defaultLimit = 80,
        private readonly string $ellipsis = '...',
    ) {}

    public function preview(string $text, ?int $limit = null): string
    {
        $limit = $limit ?? $this->defaultLimit;

        $normalized = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($normalized === '' || $limit <= 0) {
            return $normalized;
        }

        if (Str::length($normalized) <= $limit) {
            return $normalized;
        }

        $truncated = rtrim(Str::substr($normalized, 0, $limit));

        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > 0) {
            $truncated = Str::substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated).$this->ellipsis;
    }

    public function slug(string $text): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return Str::slug($normalized);
    }
}
