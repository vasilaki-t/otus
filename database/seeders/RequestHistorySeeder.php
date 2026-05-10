<?php

namespace Database\Seeders;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use Illuminate\Database\Seeder;

class RequestHistorySeeder extends Seeder
{
    public function run(): void
    {
        $messengerIds = Messenger::query()->pluck('id');

        Dialog::query()->each(function (Dialog $dialog) use ($messengerIds): void {
            RequestHistory::factory()
                ->count(5)
                ->create([
                    'dialog_id' => $dialog->id,
                    'messenger_id' => $messengerIds->random(),
                ]);
        });
    }
}
