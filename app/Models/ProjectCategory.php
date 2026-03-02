<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PMS\Requirement;

class ProjectCategory extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    protected $table ="project_categories";

    public function requirements()
    {
        return $this->hasMany(Requirement::class,'project_category_id');
    }

    public function subCategories()
    {
        return $this->hasMany(ProjectSubCategory::class,'category_id');
    }

}