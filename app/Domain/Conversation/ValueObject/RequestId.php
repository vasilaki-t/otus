<?php

declare(strict_types=1);

namespace App\Domain\Conversation\ValueObject;

use InvalidArgumentException;

/**
 * Identity of a RequestHistoryEntry inside the Dialog aggregate.
 *
 * Entries that have not been persisted yet have no stable identifier, so the
 * value object is optional at creation time (see {@see self::none()}).
 */
final readonly class RequestId
{
    private function __construct(private ?int $value)
    {
        if ($value !== null && $value <= 0) {
            throw new InvalidArgumentException('RequestId must be a positive integer.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    /**
     * A not-yet-persisted entry without a database identifier.
     */
    public static function none(): self
    {
        return new self(null);
    }

    public function isAssigned(): bool
    {
        return $this->value !== null;
    }

    public function value(): ?int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
