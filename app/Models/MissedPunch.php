<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissedPunch extends Model
{
    use HasFactory;
    protected $fillable = ['type','date','checkinTime','checkoutTime','description','status','remark','user_id','requested_at'];

}