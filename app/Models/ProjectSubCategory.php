<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PMS\Requirement;

class ProjectSubCategory extends Model
{
    protected $fillable = ['category_id', 'name', 'code', 'description'];
    protected $table="project_subcategories";

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class,'category_id');
    }
    public function requirements()
    {
        return $this->hasMany(Requirement::class,'project_subcategory_id');
    }
}