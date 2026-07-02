<?php

namespace Tests\Unit;

use App\Models\Dialog;
use App\Models\User;
use App\Policies\DialogPolicy;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit test for the DialogPolicy logic.
 *
 * Models are replaced with Mockery stubs, so the database is never touched.
 */
class DialogPolicyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private DialogPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new DialogPolicy;
    }

    /**
     * Build a User stub with the given id and isAdmin() result.
     */
    private function userStub(int $id, bool $isAdmin = false): User
    {
        /** @var User&Mockery\MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = $id;
        $user->shouldReceive('isAdmin')->andReturn($isAdmin);

        return $user;
    }

    /**
     * Build a Dialog stub owned by the given user id.
     */
    private function dialogStub(int $ownerId): Dialog
    {
        /** @var Dialog&Mockery\MockInterface $dialog */
        $dialog = Mockery::mock(Dialog::class)->makePartial();
        $dialog->user_id = $ownerId;

        return $dialog;
    }

    public function test_view_any_is_allowed_for_any_authenticated_user(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userStub(1)));
    }

    public function test_create_is_allowed_for_any_authenticated_user(): void
    {
        $this->assertTrue($this->policy->create($this->userStub(1)));
    }

    public function test_owner_can_view_update_and_delete_own_dialog(): void
    {
        $user = $this->userStub(7);
        $dialog = $this->dialogStub(7);

        $this->assertTrue($this->policy->view($user, $dialog));
        $this->assertTrue($this->policy->update($user, $dialog));
        $this->assertTrue($this->policy->delete($user, $dialog));
    }

    public function test_non_owner_cannot_view_update_or_delete_foreign_dialog(): void
    {
        $user = $this->userStub(7, isAdmin: false);
        $dialog = $this->dialogStub(99);

        $this->assertFalse($this->policy->view($user, $dialog));
        $this->assertFalse($this->policy->update($user, $dialog));
        $this->assertFalse($this->policy->delete($user, $dialog));
    }

    public function test_admin_can_manage_any_dialog(): void
    {
        $admin = $this->userStub(7, isAdmin: true);
        $dialog = $this->dialogStub(99);

        $this->assertTrue($this->policy->view($admin, $dialog));
        $this->assertTrue($this->policy->update($admin, $dialog));
        $this->assertTrue($this->policy->delete($admin, $dialog));
    }
}
