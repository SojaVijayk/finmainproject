<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'type', 'is_slab_based', 'status'];

    public function slabs()
    {
        return $this->hasMany(PayItemSlab::class)->orderBy('salary_from');
    }
}

