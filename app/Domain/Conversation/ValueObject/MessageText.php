<?php

declare(strict_types=1);

namespace App\Domain\Conversation\ValueObject;

use InvalidArgumentException;

/**
 * Text of a request or a response.
 *
 * Guards its own invariants: the text may not be blank and may not exceed
 * {@see self::MAX_LENGTH} characters. Because the value object is immutable
 * and validated in the constructor, an invalid {@see MessageText} cannot
 * exist anywhere in the domain.
 */
final readonly class MessageText
{
    public const int MAX_LENGTH = 10000;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Message text must not be empty.');
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Message text must not exceed %d characters.', self::MAX_LENGTH),
            );
        }

        $this->value = $trimmed;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
