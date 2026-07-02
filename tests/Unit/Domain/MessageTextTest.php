<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Conversation\ValueObject\MessageText;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MessageTextTest extends TestCase
{
    public function test_it_exposes_trimmed_value(): void
    {
        $text = MessageText::fromString('  hello  ');

        $this->assertSame('hello', $text->value());
    }

    public function test_it_rejects_empty_text(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MessageText::fromString('   ');
    }

    public function test_it_rejects_too_long_text(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MessageText::fromString(str_repeat('x', MessageText::MAX_LENGTH + 1));
    }

    public function test_it_accepts_text_at_max_length(): void
    {
        $value = str_repeat('x', MessageText::MAX_LENGTH);

        $this->assertSame($value, MessageText::fromString($value)->value());
    }

    public function test_equals_compares_by_value(): void
    {
        $this->assertTrue(MessageText::fromString('a')->equals(MessageText::fromString('a')));
        $this->assertFalse(MessageText::fromString('a')->equals(MessageText::fromString('b')));
    }
}
