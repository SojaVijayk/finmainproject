<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectExpenseComponent extends Model
{
    use HasFactory, SoftDeletes;
const TYPE_ESTIMATED = 1;
    const TYPE_BUDGETED = 0;
    protected $fillable = [
       'project_id',
    'expense_category_id',
    'group_name',
    'component',
    'mandays',
    'rate',
    'amount',
    'type',
        'financial_year',
        'financial_year_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

      public function isEstimated()
    {
        return $this->type === self::TYPE_ESTIMATED;
    }

    public function isBudgeted()
    {
        return $this->type === self::TYPE_BUDGETED;
    }
}
