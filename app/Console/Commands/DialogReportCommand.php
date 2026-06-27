<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Bus\GenerateDialogReport;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Carbon;
use League\Csv\Writer;
use Throwable;

/**
 * Builds an aggregated dialog-activity report for one user or for everyone.
 *
 * The heavy lifting is dispatched synchronously through the command bus
 * ({@see GenerateDialogReport}); this class only parses input, validates it and
 * renders the result in the requested format (table, json or csv).
 */
class DialogReportCommand extends Command
{
    protected $signature = 'dialogs:report
        {user? : ID пользователя (необязательно, по умолчанию все)}
        {--format=table : Формат вывода (table|json|csv)}
        {--since= : Только диалоги после даты YYYY-MM-DD}
        {--output= : Путь к файлу для сохранения CSV}';

    protected $description = 'Сводный отчёт по диалогам и сообщениям пользователей (table/json/csv через командную шину)';

    /** @var list<string> */
    private const FORMATS = ['table', 'json', 'csv'];

    public function handle(Dispatcher $bus): int
    {
        $format = strtolower((string) $this->option('format'));

        if (! in_array($format, self::FORMATS, true)) {
            $this->error(sprintf(
                'Неизвестный формат "%s". Допустимо: %s.',
                $format,
                implode(', ', self::FORMATS),
            ));

            return self::FAILURE;
        }

        $userId = $this->resolveUserId();
        if ($userId === false) {
            return self::FAILURE;
        }

        $since = $this->resolveSince();
        if ($since === false) {
            return self::FAILURE;
        }

        // Dispatch the report generation through the command bus and capture the
        // value returned by the self-handling command's handle() method.
        /** @var list<array<string, scalar>> $rows */
        $rows = $bus->dispatchSync(new GenerateDialogReport($userId, $since));

        return match ($format) {
            'json' => $this->renderJson($rows),
            'csv' => $this->renderCsv($rows),
            default => $this->renderTable($rows),
        };
    }

    /**
     * @return int|null|false user id, null for "all users", or false on error
     */
    private function resolveUserId(): int|null|false
    {
        $argument = $this->argument('user');

        if ($argument === null) {
            return null;
        }

        $userId = (int) $argument;

        if (! User::query()->whereKey($userId)->exists()) {
            $this->error(sprintf('Пользователь #%s не найден.', $argument));

            return false;
        }

        return $userId;
    }

    private function resolveSince(): CarbonInterface|null|false
    {
        $since = $this->option('since');

        if ($since === null || $since === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', (string) $since)->startOfDay();
        } catch (Throwable) {
            $this->error(sprintf('Некорректная дата --since "%s". Ожидается формат YYYY-MM-DD.', $since));

            return false;
        }
    }

    /**
     * @param  list<array<string, scalar>>  $rows
     */
    private function renderTable(array $rows): int
    {
        if ($rows === []) {
            $this->info('Нет данных для отчёта.');

            return self::SUCCESS;
        }

        $this->table(GenerateDialogReport::headers(), $rows);

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, scalar>>  $rows
     */
    private function renderJson(array $rows): int
    {
        $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, scalar>>  $rows
     */
    private function renderCsv(array $rows): int
    {
        $writer = Writer::createFromString();
        $writer->insertOne(GenerateDialogReport::headers());
        $writer->insertAll($rows);

        $csv = $writer->toString();

        $output = $this->option('output');

        if ($output === null || $output === '') {
            $this->line($csv);

            return self::SUCCESS;
        }

        $path = (string) $output;

        if (@file_put_contents($path, $csv) === false) {
            $this->error(sprintf('Не удалось записать CSV в "%s".', $path));

            return self::FAILURE;
        }

        $this->info(sprintf('CSV-отчёт сохранён: %s', $path));

        return self::SUCCESS;
    }
}
