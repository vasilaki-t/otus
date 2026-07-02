<?php

declare(strict_types=1);

namespace Vasilaki\DialogTools;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Vasilaki\DialogTools\Contracts\MessagePreviewer;

class DialogToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dialog-tools.php',
            'dialog-tools'
        );

        // Binding: contract -> implementation.
        $this->app->bind(MessagePreviewer::class, function (Application $app): MessagePreviewerService {
            /** @var array{limit?: int, ellipsis?: string} $config */
            $config = $app['config']->get('dialog-tools', []);

            return new MessagePreviewerService(
                (int) ($config['limit'] ?? 80),
                (string) ($config['ellipsis'] ?? '...'),
            );
        });

        // Singleton used by the facade accessor.
        $this->app->singleton('dialog-tools', fn (Application $app): MessagePreviewer => $app->make(MessagePreviewer::class));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/dialog-tools.php' => config_path('dialog-tools.php'),
        ], 'dialog-tools-config');
    }
}
