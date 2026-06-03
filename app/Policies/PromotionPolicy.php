<?php

namespace App\Policies;

use App\Models\Promotion;
use App\Models\User;

class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('promotion.viewAny');
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $user->can('promotion.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('promotion.create');
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $user->can('promotion.update');
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        return $user->can('promotion.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('promotion.delete');
    }
}
