<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Traits\LogsActivity;


class Proposal extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'requirement_id',
        'budget',
        'tenure_days',
        'tenure_months',
        'tenure_years',
        'expected_start_date',
        'expected_end_date',
        'estimated_expense',
        'revenue',
        'technical_details',
        'methodology',
        'status',
        'created_by',
        'client_status',
    'client_comments',
    'client_status_updated_at',
    'client_status_updated_by',
      'project_status',
    ];

    protected $casts = [
        'expected_start_date' => 'date',
        'expected_end_date' => 'date',
    ];

    // Status constants
    const STATUS_CREATED = 0;
    const STATUS_SENT_TO_DIRECTOR = 1;
    const STATUS_APPROVED_BY_DIRECTOR = 2;
    const STATUS_REJECTED = 3;
    const STATUS_RETURNED_FOR_CLARIFICATION = 4;

    // Type constants for expense components
    const TYPE_BUDGETED = 0;
    const TYPE_ESTIMATED = 1;

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(ProposalDocument::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }


//     public function workOrderDocuments()
// {
//     return $this->morphMany(ProjectDocument::class, 'documentable')->where('type', 'Work Order');
// }
public function workOrderDocuments()
{
    return $this->hasMany(ProposalDocument::class)->where('category', 'Work order');
}
    public function statusLogs()
    {
        return $this->hasMany(ProposalStatusLog::class);
    }
public function clientStatusLogs()
{
    return $this->hasMany(ProposalClientStatusLog::class);
}
    public function getStatusNameAttribute()
    {
        return [
            self::STATUS_CREATED => 'Created',
            self::STATUS_SENT_TO_DIRECTOR => 'Sent to Director',
            self::STATUS_APPROVED_BY_DIRECTOR => 'Approved by Director',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RETURNED_FOR_CLARIFICATION => 'Returned for Clarification',
        ][$this->status] ?? 'Unknown';
    }
    public static function statusNames()
{
    return [
        self::STATUS_CREATED => 'Created',
        self::STATUS_SENT_TO_DIRECTOR => 'Sent to Director',
        self::STATUS_APPROVED_BY_DIRECTOR => 'Approved by Director',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_RETURNED_FOR_CLARIFICATION => 'Returned for Clarification',
    ];
}

public function getStatusBadgeColorAttribute()
{
    return [
        self::STATUS_CREATED => 'info', // gray
        self::STATUS_SENT_TO_DIRECTOR => 'primary',     // yellow
        self::STATUS_APPROVED_BY_DIRECTOR => 'success',   // green
        self::STATUS_REJECTED => 'danger',   // green
         self::STATUS_RETURNED_FOR_CLARIFICATION => 'warning',

    ][$this->status] ?? 'dark'; // fallback
}



    public function getTenureAttribute()
    {
        $parts = [];

        if ($this->tenure_years) {
            $parts[] = $this->tenure_years . ' year' . ($this->tenure_years > 1 ? 's' : '');
        }

        if ($this->tenure_months) {
            $parts[] = $this->tenure_months . ' month' . ($this->tenure_months > 1 ? 's' : '');
        }

        if ($this->tenure_days) {
            $parts[] = $this->tenure_days . ' day' . ($this->tenure_days > 1 ? 's' : '');
        }

        return implode(', ', $parts);
    }

    public function expenseComponents()
{
    return $this->hasMany(ProposalExpenseComponent::class);
}


// // Add this method to calculate total expense from components
// public function getTotalEstimatedExpenseAttribute()
// {
//     return $this->expenseComponents->sum('amount');
// }


// Estimated expense components only (type = 1)
    public function estimatedExpenseComponents()
    {
        return $this->hasMany(ProposalExpenseComponent::class)->where('type', self::TYPE_ESTIMATED);
    }

    // Budgeted expense components only (type = 0)
    public function budgetedExpenseComponents()
    {
        return $this->hasMany(ProposalExpenseComponent::class)->where('type', self::TYPE_BUDGETED);
    }

    // Add this method to calculate total estimated expense from components
    public function getTotalEstimatedExpenseAttribute()
    {
        return $this->estimatedExpenseComponents->sum('amount');
    }

    // Add this method to calculate total budgeted expense from components
    public function getTotalBudgetedExpenseAttribute()
    {
        return $this->budgetedExpenseComponents->sum('amount');
    }

    // Calculate the difference between budgeted and estimated
    public function getExpenseDifferenceAttribute()
    {
        return $this->total_budgeted_expense - $this->total_estimated_expense;
    }

    // Helper method to get expense summary
    public function getExpenseSummaryAttribute()
    {
        return [
            'estimated' => $this->total_estimated_expense,
            'budgeted' => $this->total_budgeted_expense,
            'difference' => $this->expense_difference,
        ];
    }

    // Get grouped expense components by type
    public function getGroupedExpenseComponentsAttribute()
    {
        return [
            'estimated' => $this->estimatedExpenseComponents->groupBy('group_name'),
            'budgeted' => $this->budgetedExpenseComponents->groupBy('group_name'),
        ];
    }
}