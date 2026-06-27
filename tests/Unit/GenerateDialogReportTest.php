<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Bus\GenerateDialogReport;
use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDialogReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_aggregates_dialogs_and_messages_per_user(): void
    {
        $messenger = Messenger::factory()->create();
        $user = User::factory()->create(['username' => 'alice']);
        $dialogA = Dialog::factory()->for($user)->create();
        $dialogB = Dialog::factory()->for($user)->create();
        RequestHistory::factory()->count(2)->for($dialogA)->for($messenger)->create();
        RequestHistory::factory()->count(3)->for($dialogB)->for($messenger)->create();

        $rows = (new GenerateDialogReport($user->id))->handle();

        $this->assertCount(1, $rows);
        $this->assertSame('alice', $rows[0]['username']);
        $this->assertSame(2, $rows[0]['dialogs']);
        $this->assertSame(5, $rows[0]['messages']);
    }

    public function test_handle_returns_all_users_when_no_filter(): void
    {
        User::factory()->count(3)->create();

        $rows = (new GenerateDialogReport)->handle();

        $this->assertCount(3, $rows);
    }

    public function test_headers_match_row_keys(): void
    {
        $user = User::factory()->create();
        Dialog::factory()->for($user)->create();

        $rows = (new GenerateDialogReport($user->id))->handle();

        $this->assertSame(GenerateDialogReport::headers(), array_keys($rows[0]));
    }
}
