<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespatchType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requires_tracking',
        'requires_ack',
        'requires_mail_id',
    ];

    public function despatches()
    {
        return $this->hasMany(DocumentDespatch::class, 'type_id');
    }
}