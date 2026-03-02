<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;

class InvoicePayment extends Model
{
    use HasFactory;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];


    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
