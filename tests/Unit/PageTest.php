<?php

namespace Tests\Unit;

use App\Models\Page;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the Page model attribute casts.
 *
 * A fresh model instance is used, so no database is touched.
 */
class PageTest extends TestCase
{
    public function test_is_published_is_cast_to_boolean(): void
    {
        $this->assertTrue((new Page(['is_published' => 1]))->is_published);
        $this->assertFalse((new Page(['is_published' => 0]))->is_published);
    }

    public function test_casts_are_configured_for_publication_fields(): void
    {
        $casts = (new Page)->getCasts();

        $this->assertSame('boolean', $casts['is_published']);
        $this->assertSame('datetime', $casts['published_at']);
    }
}
