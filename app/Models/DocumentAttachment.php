<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAttachment extends Model
{
    protected $fillable = [
        'document_id', 'file_path', 'original_name',
        'mime_type', 'size','status'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}