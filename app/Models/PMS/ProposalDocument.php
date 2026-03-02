<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use App\Traits\LogsActivity;

class ProposalDocument extends Model
{
    use HasFactory;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'proposal_id',
        'name',
        'path',
        'type',
        'size',
        'category',
        'uploaded_by'
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}