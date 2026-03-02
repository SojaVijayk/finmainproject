<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TapalMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tapal_id',
        'from_user_id',
        'to_user_id',
        'remarks',
        'status',
        'is_assignment',
        'is_primary',
        'accepted_at',
        'is_accepted',
        'completed_at'
    ];

    public function tapal()
    {
        return $this->belongsTo(Tapal::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');

    }
    public function scopePrimaryAssignments($query)
{
    return $query->where('is_assignment', true)
                 ->where('is_primary', true);
}
}