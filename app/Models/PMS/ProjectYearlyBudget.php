<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Model;

class ProjectYearlyBudget extends Model
{
    protected $fillable = [
        'project_id',
       'financial_year_id',
        'amount',
          'yearly_estimated_expense',
            'yearly_revenue',
        'notes'
    ];

    protected $casts = [
        'amount' => 'float',
         'yearly_estimated_expense' => 'float', // Add this
        'yearly_revenue' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
     public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }
     public function estimatedExpenseComponents()
    {
        return $this->project->estimatedExpenseComponents()
            ->where('financial_year_id', $this->financial_year_id);
    }

    // Calculate revenue for this year
    public function getRevenueAttribute()
    {
        return $this->amount - $this->yearly_estimated_expense;
    }
}
