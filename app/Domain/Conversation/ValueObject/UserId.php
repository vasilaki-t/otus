<?php

declare(strict_types=1);

namespace App\Domain\Conversation\ValueObject;

use InvalidArgumentException;

/**
 * Reference to the owning User aggregate — held by identity only.
 *
 * The Conversation subdomain never holds a User object; it only knows the
 * identifier of the aggregate that owns a dialog. This keeps aggregate
 * boundaries strict (no cross-aggregate object references).
 */
final readonly class UserId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('UserId must be a positive integer.');
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
