<?php

namespace App\Policies;

use App\Models\CutiDokter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CutiDokterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cuti::dokter');
    }

    public function view(User $user, CutiDokter $cutiDokter): bool
    {
        return $user->can('view_cuti::dokter');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cuti::dokter');
    }

    public function update(User $user, CutiDokter $cutiDokter): bool
    {
        return $user->can('update_cuti::dokter');
    }

    public function delete(User $user, CutiDokter $cutiDokter): bool
    {
        return $user->can('delete_cuti::dokter');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cuti::dokter');
    }
}
