<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LinkLayanan;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinkLayananPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_link::layanan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('view_link::layanan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_link::layanan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('update_link::layanan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('delete_link::layanan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_link::layanan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('force_delete_link::layanan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('restore_link::layanan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LinkLayanan $linkLayanan): bool
    {
        return $user->can('replicate_link::layanan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_link::layanan');
    }
}
