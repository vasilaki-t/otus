<?php

declare(strict_types=1);

namespace Vasilaki\DialogTools\Facades;

use Illuminate\Support\Facades\Facade;
use Vasilaki\DialogTools\MessagePreviewerService;

/**
 * @method static string preview(string $text, ?int $limit = null)
 * @method static string slug(string $text)
 *
 * @see MessagePreviewerService
 */
class DialogTools extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dialog-tools';
    }
}
