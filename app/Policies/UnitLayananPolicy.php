<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UnitLayanan;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitLayananPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_unit::layanan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('view_unit::layanan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_unit::layanan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('update_unit::layanan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('delete_unit::layanan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_unit::layanan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('force_delete_unit::layanan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_unit::layanan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('restore_unit::layanan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_unit::layanan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, UnitLayanan $unitLayanan): bool
    {
        return $user->can('replicate_unit::layanan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_unit::layanan');
    }
}
