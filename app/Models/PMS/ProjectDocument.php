<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;


class ProjectDocument extends Model
{
  use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected $fillable = [
        'project_id',
        'name',
        'path',
        'type',
        'size',
        'uploaded_by',
        'folder',
        'description'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileIconAttribute()
    {
        $icons = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
        ];

        $extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        return $icons[$extension] ?? 'fa-file';
    }
}
