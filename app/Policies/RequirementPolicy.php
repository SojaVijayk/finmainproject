<?php

namespace App\Policies\PMS;

use App\Models\User;
use App\Models\PMS\Requirement;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequirementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_requirements');
    }

    public function view(User $user, Requirement $requirement)
    {
        return $user->can('view_requirements') && 
            ($requirement->created_by == $user->id || 
             $requirement->allocated_to == $user->id || 
             $user->hasRole('admin|director'));
    }

    public function create(User $user)
    {
        return $user->can('create_requirements');
    }

    public function update(User $user, Requirement $requirement)
    {
        return $user->can('edit_requirements') && 
            $requirement->status == Requirement::STATUS_INITIATED && 
            $requirement->created_by == $user->id;
    }

    public function delete(User $user, Requirement $requirement)
    {
        return $user->can('delete_requirements') && 
            $requirement->status == Requirement::STATUS_INITIATED && 
            $requirement->created_by == $user->id;
    }

    public function submitForApproval(User $user, Requirement $requirement)
    {
        return $user->can('submit_requirements') && 
            $requirement->status == Requirement::STATUS_INITIATED && 
            $requirement->created_by == $user->id;
    }

    public function approve(User $user, Requirement $requirement)
    {
        return $user->can('approve_requirements') && 
            in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR, Requirement::STATUS_SENT_TO_PAC]);
    }

    public function reject(User $user, Requirement $requirement)
    {
        return $user->can('approve_requirements') && 
            in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR, Requirement::STATUS_SENT_TO_PAC]);
    }

    public function allocate(User $user, Requirement $requirement)
    {
        return $user->can('allocate_requirements') && 
            $requirement->status == Requirement::STATUS_APPROVED_BY_DIRECTOR;
    }
}