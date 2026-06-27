<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DialogReportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_table_format_runs_successfully(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->for($user)->create();
        RequestHistory::factory()->count(3)->for($dialog)->for(Messenger::factory())->create();

        $this->artisan('dialogs:report')->assertExitCode(0);
    }

    public function test_json_format_outputs_valid_json(): void
    {
        $user = User::factory()->create(['username' => 'reporter']);
        $dialog = Dialog::factory()->for($user)->create();
        RequestHistory::factory()->count(2)->for($dialog)->for(Messenger::factory())->create();

        $exit = Artisan::call('dialogs:report', ['--format' => 'json']);
        $output = Artisan::output();

        $this->assertSame(0, $exit);

        $decoded = json_decode(trim($output), true);
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);
        $this->assertSame('reporter', $decoded[0]['username']);
        $this->assertSame(1, $decoded[0]['dialogs']);
        $this->assertSame(2, $decoded[0]['messages']);
    }

    public function test_csv_format_writes_file_with_header(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->for($user)->create();
        RequestHistory::factory()->for($dialog)->for(Messenger::factory())->create();

        $path = sys_get_temp_dir().'/dialogs-report-'.uniqid().'.csv';

        try {
            $this->artisan('dialogs:report', [
                '--format' => 'csv',
                '--output' => $path,
            ])->assertExitCode(0);

            $this->assertFileExists($path);

            $contents = (string) file_get_contents($path);
            $this->assertStringContainsString('user_id,username,dialogs,messages,last_dialog_at', $contents);
        } finally {
            @unlink($path);
        }
    }

    public function test_invalid_format_fails(): void
    {
        $this->artisan('dialogs:report', ['--format' => 'xml'])->assertExitCode(1);
    }

    public function test_missing_user_fails(): void
    {
        $this->artisan('dialogs:report', ['user' => 999999])->assertExitCode(1);
    }

    public function test_filter_by_existing_user_returns_only_their_stats(): void
    {
        $messenger = Messenger::factory()->create();
        $target = User::factory()->create(['username' => 'target']);
        $other = User::factory()->create();

        $targetDialog = Dialog::factory()->for($target)->create();
        RequestHistory::factory()->count(4)->for($targetDialog)->for($messenger)->create();

        $otherDialog = Dialog::factory()->for($other)->create();
        RequestHistory::factory()->count(7)->for($otherDialog)->for($messenger)->create();

        Artisan::call('dialogs:report', [
            'user' => $target->id,
            '--format' => 'json',
        ]);
        $decoded = json_decode(trim(Artisan::output()), true);

        $this->assertCount(1, $decoded);
        $this->assertSame($target->id, $decoded[0]['user_id']);
        $this->assertSame(4, $decoded[0]['messages']);
    }

    public function test_since_filter_excludes_older_dialogs(): void
    {
        $messenger = Messenger::factory()->create();
        $user = User::factory()->create();

        $old = Dialog::factory()->for($user)->create();
        $old->forceFill(['created_at' => now()->subDays(10)])->save();
        RequestHistory::factory()->count(2)->for($old)->for($messenger)->create();

        $recent = Dialog::factory()->for($user)->create();
        $recent->forceFill(['created_at' => now()])->save();
        RequestHistory::factory()->count(1)->for($recent)->for($messenger)->create();

        Artisan::call('dialogs:report', [
            'user' => $user->id,
            '--since' => now()->subDay()->toDateString(),
            '--format' => 'json',
        ]);
        $decoded = json_decode(trim(Artisan::output()), true);

        $this->assertSame(1, $decoded[0]['dialogs']);
        $this->assertSame(1, $decoded[0]['messages']);
    }

    public function test_invalid_since_date_fails(): void
    {
        $this->artisan('dialogs:report', ['--since' => 'not-a-date'])->assertExitCode(1);
    }
}
