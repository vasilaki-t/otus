<?php

declare(strict_types=1);

namespace App\Domain\Conversation\ValueObject;

use InvalidArgumentException;

/**
 * Reference to a Messenger (Channels subdomain) — identity only.
 *
 * Identifies through which channel a request was made without pulling the
 * Messenger aggregate into the Conversation boundary.
 */
final readonly class MessengerId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('MessengerId must be a positive integer.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
