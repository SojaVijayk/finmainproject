<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayItemSlab extends Model
{
    use HasFactory;

    protected $fillable = ['pay_item_id', 'salary_from', 'salary_to', 'amount'];

    public function payItem()
    {
        return $this->belongsTo(PayItem::class);
    }
}

