<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the User model.
 *
 * The Eloquent relation chain is replaced with Mockery stubs,
 * so isAdmin()/hasRole() are tested without any database access.
 */
class UserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_is_admin_delegates_to_has_role_with_admin_slug(): void
    {
        /** @var User&Mockery\MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->once()->with(Role::ADMIN)->andReturn(true);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_is_false_when_user_has_no_admin_role(): void
    {
        /** @var User&Mockery\MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->once()->with(Role::ADMIN)->andReturn(false);

        $this->assertFalse($user->isAdmin());
    }

    public function test_has_role_queries_the_roles_relation_for_the_given_slug(): void
    {
        /** @var BelongsToMany&Mockery\MockInterface $relation */
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('where')->once()->with('slug', 'editor')->andReturnSelf();
        $relation->shouldReceive('exists')->once()->andReturn(true);

        /** @var User&Mockery\MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('roles')->once()->andReturn($relation);

        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_has_role_returns_false_when_relation_has_no_match(): void
    {
        /** @var BelongsToMany&Mockery\MockInterface $relation */
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('where')->once()->with('slug', 'ghost')->andReturnSelf();
        $relation->shouldReceive('exists')->once()->andReturn(false);

        /** @var User&Mockery\MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('roles')->once()->andReturn($relation);

        $this->assertFalse($user->hasRole('ghost'));
    }
}
