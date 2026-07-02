<?php

namespace App\Policies;

use App\Models\Dialog;
use App\Models\User;

class DialogPolicy
{
    /**
     * Any authenticated user can list dialogs (own ones are filtered in the controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dialog $dialog): bool
    {
        return $this->owns($user, $dialog);
    }

    /**
     * Any authenticated user may create a dialog.
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Dialog $dialog): bool
    {
        return $this->owns($user, $dialog);
    }

    public function delete(User $user, Dialog $dialog): bool
    {
        return $this->owns($user, $dialog);
    }

    /**
     * The owner of the dialog or an administrator may manage it.
     */
    private function owns(User $user, Dialog $dialog): bool
    {
        return $dialog->user_id === $user->id || $user->isAdmin();
    }
}
