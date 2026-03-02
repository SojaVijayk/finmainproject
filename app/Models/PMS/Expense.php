<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'category_id',
        'vendor_id',
        'amount',
        'tax',
        'total_amount',
        'payment_mode',
        'payment_date',
        'transaction_reference',
        'notes',
        'created_by'
    ];
    protected $casts = [
            'payment_date' => 'datetime',
        ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            $expense->total_amount = $expense->amount + $expense->tax;
        });

        static::updating(function ($expense) {
            $expense->total_amount = $expense->amount + $expense->tax;
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
