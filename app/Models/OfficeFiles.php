<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeFiles extends Model
{
    use HasFactory;
    protected $fillable = ['filename','date','year','numbers','description','status','user_id'];

}
