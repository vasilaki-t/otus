<?php

namespace App\Providers;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\Page;
use App\Models\RequestHistory;
use App\Models\User;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Database\Eloquent\Model;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (self::CACHE_INVALIDATING_MODELS as $model) {
            $model::observe(CacheInvalidationObserver::class);
        }
    }
}
