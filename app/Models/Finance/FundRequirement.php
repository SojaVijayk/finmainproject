<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequirement extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ['title', 'amount', 'due_date', 'is_recurring', 'frequency', 'status', 'description'];

  protected $casts = [
    'due_date' => 'date',
    'is_recurring' => 'boolean',
  ];
}
