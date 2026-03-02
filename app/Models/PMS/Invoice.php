<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;


class Invoice extends Model
{
    use HasFactory;
    use LogsActivity;


protected static $recordEvents = ['created', 'updated', 'deleted'];



    protected $fillable = [
        'project_id',
        'milestone_id',
        'invoice_type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'tax_amount',
        'total_amount',
        'description',
        'status',
        'requested_by',
        'generated_by',
        'cancel_remark',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    // Status constants
    const STATUS_DRAFT = 0;
    const STATUS_SENT = 1;
    const STATUS_PAID = 2;
    const STATUS_OVERDUE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_CONVERTED = 5;
const STATUS_PARTIAL_CONVERTED = 6;
const STATUS_PARTIAL_NO_PROFORMA = 7;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
    public function items()
{
    return $this->hasMany(InvoiceItem::class);
}

    public function getStatusNameAttribute()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
              self::STATUS_CONVERTED => 'Converted',
                self::STATUS_PARTIAL_CONVERTED => 'Partial Converted',
                 self::STATUS_PARTIAL_NO_PROFORMA => 'Partial (No Proforma)',
        ][$this->status] ?? 'Unknown';
    }

     public function getStatusBadgeColorAttribute()
{
    return [
        self::STATUS_DRAFT => 'secondary',  // gray
        self::STATUS_SENT => 'info',    // yellow
        self::STATUS_PAID => 'success',
         self::STATUS_OVERDUE => 'warning',      // green
           self::STATUS_CANCELLED => 'danger',
            self::STATUS_CONVERTED => 'dark',
                self::STATUS_PARTIAL_CONVERTED => 'primary',
    ][$this->status] ?? 'dark'; // fallback color
}

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsPaidAttribute()
    {
        return $this->balance_amount <= 0;
    }
}