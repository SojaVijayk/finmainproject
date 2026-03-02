<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    protected $fillable = ['name', 'email', 'mobile', 'certificate_path','otp','otp_verified','certificate_generated_at'];
}
