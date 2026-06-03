<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('template.viewAny');
    }

    public function view(User $user, Template $template): bool
    {
        return $user->can('template.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('template.create');
    }

    public function update(User $user, Template $template): bool
    {
        return $user->can('template.update');
    }

    public function delete(User $user, Template $template): bool
    {
        return $user->can('template.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('template.delete');
    }
}
