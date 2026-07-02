<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * Custom Monolog factory for the "telegram" log channel.
 *
 * Behaviour:
 *  - When TELEGRAM_BOT_TOKEN or TELEGRAM_LOG_CHAT_ID is empty (tests, local
 *    setup without a bot) it returns a Logger with a NullHandler so nothing
 *    breaks and nothing is sent anywhere.
 *  - Otherwise it wraps the standard Monolog TelegramBotHandler together with a
 *    file StreamHandler inside a WhatFailureGroupHandler. WhatFailureGroupHandler
 *    swallows any exception thrown by a child handler, so when Telegram is
 *    unreachable (network error / API down) the application keeps running AND
 *    the error is still written to the fallback log file.
 */
class TelegramLoggerFactory
{
    public function __invoke(array $config): Logger
    {
        $token = $config['token'] ?? null;
        $chatId = $config['chat_id'] ?? null;
        $level = Level::fromName(is_string($config['level'] ?? null) ? $config['level'] : 'error');

        if (empty($token) || empty($chatId)) {
            return new Logger('telegram', [new NullHandler]);
        }

        $telegramHandler = new TelegramBotHandler(
            apiKey: (string) $token,
            channel: (string) $chatId,
            level: $level,
        );

        $fallbackHandler = new StreamHandler(
            stream: storage_path('logs/telegram-fallback.log'),
            level: $level,
        );

        // WhatFailureGroupHandler delegates to every child handler and never
        // re-throws: if Telegram fails the fallback file still receives the record.
        $groupHandler = new WhatFailureGroupHandler([
            $telegramHandler,
            $fallbackHandler,
        ]);

        return new Logger('telegram', [$groupHandler]);
    }
}
