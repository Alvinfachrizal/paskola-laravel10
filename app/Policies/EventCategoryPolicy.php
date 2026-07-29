<?php

namespace App\Policies;

use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk event_categories.
 * Hanya Admin dan Kepala Sekolah yang bisa CRUD kategori.
 */
class EventCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']);
    }

    public function update(User $user, EventCategory $category): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']);
    }

    public function delete(User $user, EventCategory $category): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']);
    }
}
