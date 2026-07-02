<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Conversation\ValueObject\DialogId;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\RequestId;
use App\Domain\Conversation\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IdentifiersTest extends TestCase
{
    public function test_dialog_id_equality_and_value(): void
    {
        $a = DialogId::fromInt(7);

        $this->assertSame(7, $a->value());
        $this->assertTrue($a->equals(DialogId::fromInt(7)));
        $this->assertFalse($a->equals(DialogId::fromInt(8)));
    }

    public function test_user_id_equality(): void
    {
        $this->assertTrue(UserId::fromInt(3)->equals(UserId::fromInt(3)));
        $this->assertFalse(UserId::fromInt(3)->equals(UserId::fromInt(4)));
    }

    public function test_messenger_id_value(): void
    {
        $this->assertSame(5, MessengerId::fromInt(5)->value());
    }

    public function test_ids_reject_non_positive_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DialogId::fromInt(0);
    }

    public function test_request_id_can_be_unassigned(): void
    {
        $none = RequestId::none();

        $this->assertFalse($none->isAssigned());
        $this->assertNull($none->value());

        $assigned = RequestId::fromInt(11);
        $this->assertTrue($assigned->isAssigned());
        $this->assertSame(11, $assigned->value());
    }

    public function test_value_objects_are_immutable(): void
    {
        $reflection = new \ReflectionClass(DialogId::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
