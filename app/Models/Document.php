<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_number', 'number_type', 'document_type_id', 'user_id',
        'authorized_person_id', 'code_id', 'to_address_details', 'subject',
        'project_details', 'sequence_number', 'year', 'status',
        'cancellation_reason', 'cancelled_by', 'cancelled_at','revision_reason','revision_requested_by','revision_request_at'
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function authorizedPerson()
    {
        return $this->belongsTo(User::class, 'authorized_person_id');
    }

    public function code()
    {
        return $this->belongsTo(DocumentCode::class, 'code_id');
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    public function histories()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

     public function revisionBy()
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }
public function despatches()
{
    return $this->hasMany(DocumentDespatch::class, 'document_id');
}

}
