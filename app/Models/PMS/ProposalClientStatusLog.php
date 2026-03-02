<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class ProposalClientStatusLog extends Model
{
   use HasFactory;

    protected $fillable = [
        'proposal_id',
        'from_status',
        'to_status',
        'comments',
        'changed_by',
    ];

    /**
     * Relationship with Proposal
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * User who changed the status
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
