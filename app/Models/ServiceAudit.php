<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'field_name',
        'old_value',
        'new_value',
        'updated_by'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
