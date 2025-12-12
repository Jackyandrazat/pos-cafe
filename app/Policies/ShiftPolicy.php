<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('owner') || $user->hasRole('kasir');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shift $shift): bool
    {
        return $user->hasRole('admin') || $user->hasRole('owner') || $user->hasRole('kasir');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('kasir') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shift $shift): bool
    {
        return $user->hasRole('kasir') || $user->hasRole('admin') || $user->hasRole('owner');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shift $shift): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shift $shift): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shift $shift): bool
    {
        return $user->hasRole('admin');
    }
}
