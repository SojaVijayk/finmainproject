<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposalExpenseComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
         'proposal_id',
    'expense_category_id',
    'group_name',
    'component',
    'mandays',
    'rate',
    'amount',
    'type', // 0=budgeted, 1=estimated
    ];
    const TYPE_ESTIMATED = 1;
    const TYPE_BUDGETED = 0;

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2'
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    // Scope for budgeted expenses
    public function scopeBudgeted($query)
    {
        return $query->where('type', self::TYPE_BUDGETED);
    }

    // Scope for estimated expenses
    public function scopeEstimated($query)
    {
        return $query->where('type', self::TYPE_ESTIMATED);
    }

    // Accessor for type name
    public function getTypeNameAttribute()
    {
        return [
            self::TYPE_BUDGETED => 'Budgeted',
            self::TYPE_ESTIMATED => 'Estimated',
        ][$this->type] ?? 'Unknown';
    }

    // Accessor for calculated amount (if rate and mandays are provided)
    public function getCalculatedAmountAttribute()
    {
        if ($this->rate && $this->mandays) {
            return $this->rate * $this->mandays;
        }
        return $this->amount;
    }

    // Check if component is calculated (HR components)
    public function getIsCalculatedAttribute()
    {
        return !is_null($this->rate) && !is_null($this->mandays);
    }
}