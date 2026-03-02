<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_name',
        'description',
        'event',
        'subject_id',
        'subject_type',
        'user_id',
        'properties'
    ];
    
    protected $casts = [
        'properties' => 'array'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function subject()
    {
        return $this->morphTo();
    }
}