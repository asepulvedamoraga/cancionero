<?php

namespace App\Policies;

use App\Models\Song;
use App\Models\User;

class SongPolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Song $song): bool
    {
        return $song->is_active || $song->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Song $song): bool
    {
        return $song->user_id === $user->id;
    }

    public function restore(User $user, Song $song): bool
    {
        return $song->user_id === $user->id;
    }

    public function delete(User $user, Song $song): bool
    {
        return $song->user_id === $user->id;
    }
}
