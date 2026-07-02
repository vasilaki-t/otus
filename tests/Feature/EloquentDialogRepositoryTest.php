<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Conversation\Dialog;
use App\Domain\Conversation\DialogRepository;
use App\Domain\Conversation\ValueObject\MessageText;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\UserId;
use App\Models\Messenger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentDialogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_and_find_by_id_round_trip(): void
    {
        $repository = $this->app->make(DialogRepository::class);

        $user = User::factory()->create();
        $messenger = Messenger::factory()->create();

        $dialog = Dialog::start(
            $repository->nextIdentity(),
            UserId::fromInt((int) $user->getKey()),
            'Support chat',
        );
        $dialog->addRequest(MessageText::fromString('How do I reset my password?'), MessengerId::fromInt((int) $messenger->getKey()));
        $dialog->attachResponseToLatest(MessageText::fromString('Use the reset link in settings.'));
        $dialog->addRequest(MessageText::fromString('Thanks!'), MessengerId::fromInt((int) $messenger->getKey()));

        $saved = $repository->save($dialog);

        $loaded = $repository->findById($saved->id());

        $this->assertNotNull($loaded);
        $this->assertTrue($loaded->id()->equals($saved->id()));
        $this->assertTrue($loaded->ownerId()->equals(UserId::fromInt((int) $user->getKey())));
        $this->assertSame('Support chat', $loaded->title());
        $this->assertSame(2, $loaded->requestCount());

        [$first, $second] = $loaded->requests();

        $this->assertSame('How do I reset my password?', $first->request()->value());
        $this->assertSame('Use the reset link in settings.', $first->response()?->value());
        $this->assertTrue($first->messengerId()->equals(MessengerId::fromInt((int) $messenger->getKey())));

        $this->assertSame('Thanks!', $second->request()->value());
        $this->assertNull($second->response());
        $this->assertTrue($first->id()->isAssigned());
    }

    public function test_find_by_id_returns_null_when_missing(): void
    {
        $repository = $this->app->make(DialogRepository::class);

        $this->assertNull($repository->findById($repository->nextIdentity()));
    }
}
