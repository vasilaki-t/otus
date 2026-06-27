<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Conversation\Dialog;
use App\Domain\Conversation\ValueObject\DialogId;
use App\Domain\Conversation\ValueObject\MessageText;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\UserId;
use DomainException;
use PHPUnit\Framework\TestCase;

final class DialogAggregateTest extends TestCase
{
    public function test_start_creates_valid_initial_state(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(42), '  My chat  ');

        $this->assertTrue($dialog->id()->equals(DialogId::fromInt(1)));
        $this->assertTrue($dialog->ownerId()->equals(UserId::fromInt(42)));
        $this->assertSame('My chat', $dialog->title());
        $this->assertSame(0, $dialog->requestCount());
        $this->assertSame([], $dialog->requests());
    }

    public function test_owner_is_referenced_by_user_id_value_object(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(9));

        $this->assertInstanceOf(UserId::class, $dialog->ownerId());
        $this->assertNull($dialog->title());
    }

    public function test_add_request_appends_entry(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(1));

        $entry = $dialog->addRequest(MessageText::fromString('Hello?'), MessengerId::fromInt(2));

        $this->assertSame(1, $dialog->requestCount());
        $this->assertSame('Hello?', $entry->request()->value());
        $this->assertFalse($entry->id()->isAssigned());
        $this->assertFalse($entry->hasResponse());
        $this->assertTrue($entry->messengerId()->equals(MessengerId::fromInt(2)));
    }

    public function test_requests_getter_returns_defensive_copy(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(1));
        $dialog->addRequest(MessageText::fromString('Hi'), MessengerId::fromInt(1));

        $copy = $dialog->requests();
        $copy[] = 'tampered';

        $this->assertSame(1, $dialog->requestCount());
    }

    public function test_attach_response_to_latest_sets_response(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(1));
        $dialog->addRequest(MessageText::fromString('Question'), MessengerId::fromInt(1));

        $dialog->attachResponseToLatest(MessageText::fromString('Answer'));

        $this->assertSame('Answer', $dialog->latestRequest()?->response()?->value());
    }

    public function test_attach_response_twice_violates_invariant(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(1));
        $dialog->addRequest(MessageText::fromString('Question'), MessengerId::fromInt(1));
        $dialog->attachResponseToLatest(MessageText::fromString('Answer'));

        $this->expectException(DomainException::class);

        $dialog->attachResponseToLatest(MessageText::fromString('Second answer'));
    }

    public function test_attach_response_without_request_violates_invariant(): void
    {
        $dialog = Dialog::start(DialogId::fromInt(1), UserId::fromInt(1));

        $this->expectException(DomainException::class);

        $dialog->attachResponseToLatest(MessageText::fromString('Answer'));
    }

    public function test_aggregate_root_has_no_public_setters(): void
    {
        $reflection = new \ReflectionClass(Dialog::class);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->assertStringStartsNotWith('set', $method->getName());
        }
    }
}
