<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Logging\TelegramLoggerFactory;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use Tests\TestCase;

class TelegramLoggerFactoryTest extends TestCase
{
    public function test_returns_null_handler_logger_when_credentials_are_missing(): void
    {
        $factory = new TelegramLoggerFactory;

        $logger = $factory([
            'level' => 'error',
            'token' => null,
            'chat_id' => null,
        ]);

        $this->assertInstanceOf(Logger::class, $logger);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(NullHandler::class, $handlers[0]);

        // No Telegram handler must be present without credentials.
        foreach ($handlers as $handler) {
            $this->assertNotInstanceOf(TelegramBotHandler::class, $handler);
        }
    }

    public function test_returns_empty_token_safely(): void
    {
        $factory = new TelegramLoggerFactory;

        $logger = $factory([
            'level' => 'error',
            'token' => '',
            'chat_id' => '123',
        ]);

        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
    }

    public function test_wraps_telegram_handler_in_failure_safe_group_when_configured(): void
    {
        $factory = new TelegramLoggerFactory;

        $logger = $factory([
            'level' => 'error',
            'token' => 'dummy-token',
            'chat_id' => 'dummy-chat',
        ]);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        // The single handler must be failure-safe so a Telegram outage never
        // bubbles up and the file fallback still receives the record.
        $group = $handlers[0];
        $this->assertInstanceOf(WhatFailureGroupHandler::class, $group);

        // The group must contain the standard Monolog Telegram handler.
        $reflection = new \ReflectionClass($group);
        $property = $reflection->getProperty('handlers');
        $property->setAccessible(true);
        $children = $property->getValue($group);

        $hasTelegram = false;
        foreach ($children as $child) {
            if ($child instanceof TelegramBotHandler) {
                $hasTelegram = true;
            }
        }
        $this->assertTrue($hasTelegram, 'Group should contain a TelegramBotHandler.');
    }
}
