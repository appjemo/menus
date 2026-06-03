<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product.viewAny');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('product.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('product.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('product.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('product.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('product.delete');
    }
}
