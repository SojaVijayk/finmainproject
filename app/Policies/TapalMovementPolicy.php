<?php

namespace App\Policies;

use App\Models\TapalMovement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TapalMovementPolicy
{
    use HandlesAuthorization;

    public function accept(User $user, TapalMovement $movement)
    {
        return $movement->to_user_id == $user->id && $movement->status == 'Pending';
    }

    public function complete(User $user, TapalMovement $movement)
{
    // Only allow completion if:
    // 1. User is the current assignee
    // 2. Status is not already completed
    return $movement->to_user_id == $user->id &&
           $movement->status != 'Completed';
}
}
