<?php

namespace App\Policies;

use App\Models\Screen;
use App\Models\User;

class ScreenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('screen.viewAny');
    }

    public function view(User $user, Screen $screen): bool
    {
        return $user->can('screen.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('screen.create');
    }

    public function update(User $user, Screen $screen): bool
    {
        return $user->can('screen.update');
    }

    public function delete(User $user, Screen $screen): bool
    {
        return $user->can('screen.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('screen.delete');
    }
}
