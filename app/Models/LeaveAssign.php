<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAssign extends Model
{
    use HasFactory;
    protected $fillable = ['leave_type','total_credit','employment_type','status'];
}