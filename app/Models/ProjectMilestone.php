<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class ProjectMilestone extends Model
{
  protected $fillable = [
    'milestone',
    'due_date',
    'project_id',
    'created_by',
    'achived_at'
];
    use HasFactory;
    public function project()
    {
        return $this->belongsTo(Project::class,'id');
    }
}