<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class ClientContactPerson extends Model
{
    use HasFactory;
    protected $fillable = ['client_id','name','designation','email','address','mobile'];
    public function client()
    {
        return $this->belongsTo(Client::class,'id');
    }
}