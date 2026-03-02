<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PMS\Requirement;

class Client extends Model
{
    use HasFactory;
    protected $fillable = ['client_name','code','email','address','phone'];
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'clients_projects', 'client_id','project_id');
    }
    public function contactPersons()
    {
        return $this->hasMany(ClientContactPerson::class,'client_id');
    }
    public function requirements()
{
    return $this->hasMany(Requirement::class, 'client_id');
}
}
