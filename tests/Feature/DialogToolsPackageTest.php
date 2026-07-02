<?php

namespace Tests\Feature;

use App\Models\RequestHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vasilaki\DialogTools\Contracts\MessagePreviewer;
use Vasilaki\DialogTools\MessagePreviewerService;

class DialogToolsPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_resolves_to_the_package_implementation(): void
    {
        $previewer = app(MessagePreviewer::class);

        $this->assertInstanceOf(MessagePreviewerService::class, $previewer);
    }

    public function test_preview_keeps_short_text_and_truncates_long_text(): void
    {
        $previewer = app(MessagePreviewer::class);

        $this->assertSame('Short message', $previewer->preview('Short message'));

        $long = 'This is a fairly long request message that should be truncated on a word boundary somewhere';
        $preview = $previewer->preview($long, 30);

        $this->assertStringEndsWith('...', $preview);
        $this->assertLessThanOrEqual(33, mb_strlen($preview));
        $this->assertStringStartsWith('This is a fairly long', $preview);
    }

    public function test_request_history_exposes_package_powered_preview(): void
    {
        $history = RequestHistory::factory()->create([
            'request_text' => 'This is a long dialog request that the dialog-tools package should shorten nicely for the admin table view',
        ]);

        $this->assertStringEndsWith('...', $history->request_preview);
        $this->assertStringStartsWith('This is a long dialog request', $history->request_preview);
    }
}
