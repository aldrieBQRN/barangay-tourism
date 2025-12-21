<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Accommodation;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccommodationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_accommodation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Accommodation $accommodation): bool
    {
        return $user->can('view_accommodation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_accommodation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Accommodation $accommodation): bool
    {
        return $user->can('update_accommodation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Accommodation $accommodation): bool
    {
        return $user->can('delete_accommodation');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_accommodation');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Accommodation $accommodation): bool
    {
        return $user->can('force_delete_accommodation');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_accommodation');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Accommodation $accommodation): bool
    {
        return $user->can('restore_accommodation');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_accommodation');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Accommodation $accommodation): bool
    {
        return $user->can('replicate_accommodation');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_accommodation');
    }
}
