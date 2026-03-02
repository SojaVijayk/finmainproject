<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'service';
    protected $guarded = [];

    public function audits()
    {
        return $this->hasMany(ServiceAudit::class)->orderBy('created_at', 'desc');
    }
}
