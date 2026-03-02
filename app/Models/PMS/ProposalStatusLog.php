<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProposalStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'from_status',
        'to_status',
        'comments',
        'changed_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromStatusNameAttribute()
    {
        return Proposal::statusNames()[$this->from_status] ?? 'Unknown';
    }

    public function getToStatusNameAttribute()
    {
        return Proposal::statusNames()[$this->to_status] ?? 'Unknown';
    }
}
