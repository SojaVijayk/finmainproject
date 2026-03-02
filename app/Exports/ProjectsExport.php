<?php

namespace App\Exports;

use App\Models\PMS\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Project::with(['requirement.client', 'requirement', 'investigator', 'expenses', 'invoices.payments']);
  $user = Auth::user();
        // Filters
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->filled('investigator_id')) {
            $query->where('project_investigator_id', $this->request->investigator_id);
        }
        if ($this->request->filled('client_id')) {
            $query->whereHas('requirement', function ($q) {
                $q->where('client_id', $this->request->client_id);
            });
        }
          if ($user->hasRole('director') || $user->hasRole('finance') || $user->hasRole('Project Report')) {

            // $investigatorId =$user->id;
            //    $query->where('project_investigator_id', $investigatorId);
            if ($this->request->filled('investigator_id')) {
                $query->where('project_investigator_id', $this->request->investigator_id);
             }
    }
      else{
            $investigatorId =$user->id;
            $query->where('project_investigator_id', $investigatorId);
      }

        $projects = $query->get();

        return $projects->map(function ($project) {
            $budget = $project->budget ?? 0;
             $estimated_expense=$project->estimated_expense ?? 0;
            $expenses = $project->expenses->sum('total_amount');
            $totalInvoiced = $project->invoices->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])->sum('total_amount');
              $totalInvoiced_proforma = $project->invoices->where('invoice_type',1)->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])->sum('total_amount');
             $totalInvoiced_tax = $project->invoices->where('invoice_type',2)->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])->sum('total_amount');
              $totalPaid = $project->invoices->sum(function ($invoice) {
                return $invoice->payments->sum('amount');
            });

            return [
                'Project Title' => $project->title,
                'Project Code' => $project->project_code,
                'Client' => $project->requirement->client->client_name ?? 'N/A',
                'Category'=>$project->requirement->category->name,
                'Investigator' => $project->investigator->name ?? 'N/A',
                'Status' => $project->status_name,
                'Budget' => $budget,
                 'Estimated Expense' => $estimated_expense,
                'Actual Expenses' => $expenses,
                'Remaining Budget' => $budget - $expenses,
                'Budget Utilization %' => $budget > 0 ? round(($expenses / $budget) * 100, 2) : 0,
                'Total Invoiced' => $totalInvoiced,
                'Proforma Invoiced' => $totalInvoiced_proforma,
                'Tax Invoiced' => $totalInvoiced_tax,
                'Paid' => $totalPaid,
                'Outstanding' => $totalInvoiced_tax - $totalPaid,
                'Completion %' => $project->completion_percentage,
                'Start Date' => $project->start_date?->format('Y-m-d'),
                'End Date' => $project->end_date?->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Project Title', 'Project Code', 'Client','Category', 'Investigator', 'Status',
            'Budget','Estimated Expenses', 'Actual Expenses', 'Remaining Budget', 'Budget Utilization %',
            'Total Invoiced','Proforma Invoiced','Tax Invoiced', 'Paid', 'Outstanding', 'Completion %', 'Start Date', 'End Date'
        ];
    }
}
