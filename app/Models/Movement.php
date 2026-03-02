<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
  use HasFactory;
  protected $fillable = [
    'title',
    'type',
    'start_date',
    'start_time',
    'end_date',
    'end_time',
    'location',
    'description',
    'report',
    'status',
    'remark',
    'user_id',
    'requested_at',
    'report_updated_at',
    'reference_type',
    'reference_id',
  ];
}
