<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCode extends Model
{
    protected $fillable = ['user_id', 'code', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
