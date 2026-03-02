<?php

namespace App\Policies;

use App\Models\Tapal;
use App\Models\User;
use App\Models\TapalAttachment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TapalPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Tapal $tapal)
    {
        return $tapal->created_by == $user->id ||
               $tapal->movements()->where('to_user_id', $user->id)->exists();
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Tapal $tapal)
    {
        return $tapal->created_by == $user->id;
    }

    public function delete(User $user, Tapal $tapal)
    {
        return $tapal->created_by == $user->id;
    }

    public function forward(User $user, Tapal $tapal)
    {
        // return $tapal->current_holder_id == $user->id;
          return $tapal->created_by == $user->id ||
               $tapal->movements()->where('is_assignment', true)->exists();
    }
    public function deleteAttachment(User $user, TapalAttachment $attachment)
    {
        // Only allow deletion if user created the tapal
        return $attachment->tapal->created_by == $user->id;
    }
    public function accept(User $user, Tapal $tapal)
{
    return $tapal->canAccept($user->id);
}

public function complete(User $user, Tapal $tapal)
{
    return $tapal->current_holder_id === $user->id;
}
}