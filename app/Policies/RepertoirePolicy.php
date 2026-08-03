<?php

namespace App\Policies;

use App\Models\Repertoire;
use App\Models\User;

class RepertoirePolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Repertoire $repertoire): bool
    {
        return $repertoire->user_id === $user->id || $repertoire->visibility === 'public';
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Repertoire $repertoire): bool
    {
        return $repertoire->user_id === $user->id;
    }

    public function delete(User $user, Repertoire $repertoire): bool
    {
        return $repertoire->user_id === $user->id;
    }

    public function restore(User $user, Repertoire $repertoire): bool
    {
        return $repertoire->user_id === $user->id;
    }

    public function duplicate(User $user, Repertoire $repertoire): bool
    {
        return $this->update($user, $repertoire);
    }

    public function export(User $user, Repertoire $repertoire): bool
    {
        return $this->update($user, $repertoire);
    }
}
