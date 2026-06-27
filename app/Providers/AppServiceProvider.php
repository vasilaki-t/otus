<?php

namespace App\Providers;

use App\Domain\Conversation\DialogRepository;
use App\Events\DialogCreated;
use App\Infrastructure\Conversation\EloquentDialogRepository;
use App\Listeners\LogDialogActivity;
use App\Listeners\SendDialogCreatedEmail;
use App\Listeners\SendTelegramDialogNotification;
use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\Page;
use App\Models\RequestHistory;
use App\Models\User;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Models whose changes invalidate the logic-level statistics cache.
     *
     * @var list<class-string<Model>>
     */
    private const CACHE_INVALIDATING_MODELS = [
        Page::class,
        Messenger::class,
        Dialog::class,
        RequestHistory::class,
        User::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // DDD: the Conversation domain depends on the DialogRepository
        // abstraction; the Eloquent mapping is wired in here as a detail.
        $this->app->bind(DialogRepository::class, EloquentDialogRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (self::CACHE_INVALIDATING_MODELS as $model) {
            $model::observe(CacheInvalidationObserver::class);
        }

        // Explicit event -> listeners wiring for the queued notification pipeline.
        // Event auto-discovery is not enabled (no withEvents() in bootstrap/app.php),
        // so registering here makes the mapping obvious and reliable.
        Event::listen(DialogCreated::class, SendTelegramDialogNotification::class);
        Event::listen(DialogCreated::class, SendDialogCreatedEmail::class);
        Event::listen(DialogCreated::class, LogDialogActivity::class);
    }
}
