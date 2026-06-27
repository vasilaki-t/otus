<?php

declare(strict_types=1);

namespace App\Bus;

use App\Models\Dialog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Self-handling command bus message that aggregates dialog statistics.
 *
 * Dispatched synchronously from the console command so the artisan layer stays
 * thin while the data-collection logic is reusable and unit-testable on its own.
 *
 * @phpstan-type ReportRow array{user_id: int, username: string, dialogs: int, messages: int, last_dialog_at: string}
 */
class GenerateDialogReport
{
    use Dispatchable;

    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?CarbonInterface $since = null,
    ) {}

    /**
     * Build the report grouped by user.
     *
     * @return list<ReportRow>
     */
    public function handle(): array
    {
        $users = User::query()
            ->when($this->userId !== null, fn ($query) => $query->whereKey($this->userId))
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($users as $user) {
            $dialogs = Dialog::query()
                ->where('user_id', $user->getKey())
                ->when(
                    $this->since !== null,
                    fn ($query) => $query->where('created_at', '>=', $this->since),
                )
                ->withCount('requestHistories')
                ->get();

            $messages = (int) $dialogs->sum('request_histories_count');

            $lastDialogAt = $dialogs
                ->pluck('updated_at')
                ->filter()
                ->max();

            $rows[] = [
                'user_id' => (int) $user->getKey(),
                'username' => (string) ($user->username ?? $user->email ?? 'n/a'),
                'dialogs' => $dialogs->count(),
                'messages' => $messages,
                'last_dialog_at' => $lastDialogAt instanceof CarbonInterface
                    ? $lastDialogAt->toDateTimeString()
                    : '—',
            ];
        }

        return $rows;
    }

    /**
     * Column headers shared by every output format.
     *
     * @return list<string>
     */
    public static function headers(): array
    {
        return ['user_id', 'username', 'dialogs', 'messages', 'last_dialog_at'];
    }
}
