<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_details'
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
