<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Document $document)
    {
        return $user->id === $document->user_id
            || $user->id === $document->authorized_person_id
            || $user->hasRole('admin');
    }

    public function cancel(User $user)
    {
        return $user->can('cancel-documents');
    }
    public function update(User $user, Document $document)
{
    return $user->id === $document->user_id || // Creator
           $user->id === $document->authorized_person_id || // Authorized person
           $user->id === $document->code->user_id; // Code owner
}
public function removeAttachment(User $user, Document $document)
{
    // Only allow if document is not confirmed and user is the creator
    return ($document->status === 'created' || $document->status === 'revised') && ($user->id === $document->user_id || $user->id === $document->authorized_person_id || // Authorized person
           $user->id === $document->code->user_id);
}
}
