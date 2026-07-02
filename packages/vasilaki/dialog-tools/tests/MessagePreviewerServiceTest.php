<?php

declare(strict_types=1);

namespace Vasilaki\DialogTools\Tests;

use PHPUnit\Framework\TestCase;
use Vasilaki\DialogTools\MessagePreviewerService;

class MessagePreviewerServiceTest extends TestCase
{
    public function test_short_text_is_returned_untouched(): void
    {
        $service = new MessagePreviewerService(80);

        $this->assertSame('Hello world', $service->preview('Hello world'));
    }

    public function test_long_text_is_truncated_on_word_boundary(): void
    {
        $service = new MessagePreviewerService(10);

        $this->assertSame('Hello...', $service->preview('Hello wonderful world'));
    }

    public function test_whitespace_is_normalized(): void
    {
        $service = new MessagePreviewerService(80);

        $this->assertSame('Hello world', $service->preview("Hello   \n  world"));
    }

    public function test_slug_is_generated(): void
    {
        $service = new MessagePreviewerService;

        $this->assertSame('hello-world', $service->slug('Hello, World!'));
    }
}
