<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Psy\TabCompletion\Matcher\FunctionsMatcher;
use App\Traits\LogsActivity;


class Project extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'project_code',
        'requirement_id',
        'proposal_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'budget',
        'estimated_expense',
        'revenue',
        'status',
        'project_investigator_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Status constants
    const STATUS_INITIATED = 0;
    const STATUS_ONGOING = 1;
    const STATUS_COMPLETED = 2;
     const STATUS_ARCHIVED = 3;

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'project_investigator_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(ProjectTeamMember::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
       public function invoice()
{
    return $this->hasMany(Invoice::class);
}

       public function expenses()
{
    return $this->hasMany(Expense::class);
}

    public function tasks()
{
    return $this->hasManyThrough(Task::class, Milestone::class);
}
    // public function documents()
    // {
    //     return $this->morphMany(ProjectDocument::class, 'documentable');
    // }
    public function documents(){
       return $this->hasMany(ProjectDocument::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function timesheets()
{
    return $this->hasMany(Timesheet::class);
}

    public function getStatusNameAttribute()
    {
        return [
            self::STATUS_INITIATED => 'Initiated',
            self::STATUS_ONGOING => 'Ongoing',
            self::STATUS_COMPLETED => 'Completed',
              self::STATUS_ARCHIVED => 'Archived',
        ][$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeColorAttribute()
{
    return [
        self::STATUS_INITIATED => 'info', // gray
        self::STATUS_ONGOING => 'warning',     // yellow
        self::STATUS_COMPLETED => 'success',   // green
        self::STATUS_ARCHIVED => 'secondary',
    ][$this->status] ?? 'dark'; // fallback
}

    public function getCompletionPercentageAttribute()
    {
        $totalWeightage = $this->milestones->sum('weightage');
        if ($totalWeightage == 0) return 0;

        $completedWeightage = $this->milestones->where('status', Milestone::STATUS_COMPLETED)->sum('weightage');
        return round(($completedWeightage / $totalWeightage) * 100, 2);
    }

    public function getTotalInvoicedAttribute()
{
    return $this->invoices->sum('amount');
}

public function getTotalPaidAttribute()
{
    return $this->invoices->sum(function($invoice) {
        return $invoice->payments->sum('amount');
    });
}

public function getTotalExpensesAttribute()
{
    return $this->expenses->sum('total_amount');
}

public function expenseComponents()
{
    return $this->hasMany(ProjectExpenseComponent::class);
}

// Add this method to calculate total expense from components
public function getTotalEstimatedExpenseAttribute()
{
    return $this->expenseComponents->sum('amount');
}

// Add this method to get proposal expense components (for initial creation)
public function getProposalExpenseComponentsAttribute()
{
    return $this->proposal ? $this->proposal->expenseComponents : collect();
}


}