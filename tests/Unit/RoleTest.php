<?php

namespace Tests\Unit;

use App\Models\Role;
use PHPUnit\Framework\TestCase;

/**
 * Trivial unit test for the Role model constants (no database).
 */
class RoleTest extends TestCase
{
    public function test_role_slug_constants_have_expected_values(): void
    {
        $this->assertSame('admin', Role::ADMIN);
        $this->assertSame('user', Role::USER);
    }
}
