<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;


class RequirementDocument extends Model
{
    use HasFactory;
     use LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'requirement_id',
        'name',
        'path',
        'type',
        'size',
        'uploaded_by',
        'source'
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
