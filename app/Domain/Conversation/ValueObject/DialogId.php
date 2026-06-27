<?php

declare(strict_types=1);

namespace App\Domain\Conversation\ValueObject;

use InvalidArgumentException;

/**
 * Identity of the Dialog aggregate root.
 *
 * Immutable wrapper around the persistence identifier. Comparing identities
 * is done with {@see self::equals()} rather than object reference, so two
 * value objects describing the same dialog are interchangeable.
 */
final readonly class DialogId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('DialogId must be a positive integer.');
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
