<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDespatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'despatch_date',
        'actual_despatch_date',
        'type_id',
        'mail_id',
        'courier_name',
        'send_by',
        'tracking_number',
        'acknowledgement_file',
        'despatch_receipt',
        'created_by',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function type()
{
    return $this->belongsTo(\App\Models\DespatchType::class, 'type_id');
}
}