<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Project;
use App\Models\PMS\Timesheet;
use App\Models\PMS\Invoice;
use App\Models\PMS\Proposal;
use App\Models\PMS\Requirement;
use App\Models\User;
use App\Models\Client;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function projectStatus(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
       $user = Auth::user();
        $statusFilter = $request->input('status', 'all');
        $dateRange = $request->input('date_range', 'this_year');

        $query = Project::query();

          if (!$user->hasRole('director') && !$user->hasRole('finance') && !$user->hasRole('Project Report')) {

            $investigatorId =$user->id;
               $query->where('project_investigator_id', $investigatorId);
          }

        // Apply status filter
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Apply date range filter
        $query = $this->applyDateRangeFilter($query, $dateRange);

        $projects = $query->with(['requirement.client', 'proposal'])->get();

        $statuses = [
            'all' => 'All Statuses',
            Project::STATUS_INITIATED => 'Initiated',
            Project::STATUS_ONGOING => 'Ongoing',
            Project::STATUS_COMPLETED => 'Completed'
        ];

        $dateRanges = [
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            'last_week' => 'Last Week',
            'last_month' => 'Last Month',
            'last_quarter' => 'Last Quarter',
            'last_year' => 'Last Year',
            'custom' => 'Custom Range'
        ];
         return view('pms.reports.project-status', compact('projects', 'statuses', 'statusFilter', 'dateRanges', 'dateRange'),['pageConfigs'=> $pageConfigs]);


      }
public function projectStatusDetailed(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];

    $user = Auth::user();

    $categoryId = $request->input('category_id');
    if ($user->hasRole('director') || $user->hasRole('finance') || $user->hasRole('Project Report') || $user->hasRole('Project Investigator')) {
        $investigatorId = $request->input('investigator_id');
    } else {
        $investigatorId = $user->id;
    }
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $liveProjects = Project::where('status', Project::STATUS_ONGOING)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $completedProjects = Project::where('status', Project::STATUS_COMPLETED)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $ongoingProjects = Project::where('status', Project::STATUS_ONGOING)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $delayedProjects = Project::where('status', Project::STATUS_ONGOING)
        ->where('end_date', '<', Carbon::today())
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $archived = Project::where('status', Project::STATUS_ARCHIVED)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    // Proposal Submitted
    $proposalSubmitted_query = Proposal::where('project_status', 0)
        ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('expected_start_date', [$startDate, $endDate]))
        ->where('project_status', 0)
        ->get();
    $proposalSubmitted = $proposalSubmitted_query->count();

    // Grouping by category
    $categoryWise_proposalSubmitted = $proposalSubmitted_query->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary_proposalSubmitted = $categoryWise_proposalSubmitted->map(function ($group) {
        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'Accepted' => $group->where('client_status', 'accepted')->count(),
            'Rejected' => $group->where('client_status', 'rejected')->count(),
            'resubmit_requested' => $group->where('client_status', 'resubmit_requested')->count(),
            'submitted' => $group->whereNull('client_status')->count(),
        ];
    });

    // Planning Stage
    $planningStage_query = Requirement::where('proposal_status', 0)
        ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
        ->when($categoryId, fn($q) => $q->where('project_category_id', $categoryId))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
        ->get();
    $planningStage = $planningStage_query->count();

    $categoryWise_planningStage = $planningStage_query->groupBy(fn($p) => $p->category->name ?? 'Uncategorized');

    $categorySummary_planningStage = $categoryWise_planningStage->map(function ($group) {
        return [
            'created' => $group->where('status', 0)->count(),
            'submitted' => $group->where('status', 1)->count(),
            'under_pac' => $group->where('status', 4)->count(),
            'rejected' => $group->where('status', 3)->count(),
            'approved' => $group->whereNotNull('allocated_to')->count(),
        ];
    });

    // Base query with relationships - Eager load invoices with date filter
    $projectsQuery = Project::with(['requirement.category', 'proposal', 'investigator', 'yearlyBudgets.financialYear', 'expenseComponents'])
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->whereIn('status', [Project::STATUS_ONGOING, Project::STATUS_COMPLETED])
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        );

    // Load projects with filtered invoices and their payments
    $projects = $projectsQuery->get()->map(function ($project) use ($startDate, $endDate) {
        // Load invoices filtered by date
        $project->setRelation('invoices',
            $project->invoices()
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('invoice_date', [$startDate, $endDate]);
                })
                ->with('payments') // Load payments for the filtered invoices
                ->get()
        );
        return $project;
    });
    // print_r($projects->where('id',876));exit;

    // Get all unique financial years from projects (sorted descending - newest first)
    $allFinancialYears = collect();
    foreach ($projects as $project) {
        foreach ($project->yearlyBudgets as $yb) {
            if ($yb->financialYear) {
                $allFinancialYears->push($yb->financialYear);
            }
        }
    }
    $allFinancialYearsFromProjects = $allFinancialYears->unique('id')->sortByDesc('start_date')->values();

    // Get all financial years from database for filter dropdown
    $allFinancialYearsForFilter = \App\Models\PMS\FinancialYear::orderBy('start_date', 'desc')->get();

    // Filter financial years based on user selection
    $selectedFinancialYearIds = $request->input('financial_year_ids', []);
    if (!empty($selectedFinancialYearIds)) {
        // User has selected specific years
        $financialYears = $allFinancialYearsFromProjects->whereIn('id', $selectedFinancialYearIds)->values();
    } else {
        // No selection, show all years from projects
        $financialYears = $allFinancialYearsFromProjects;
    }

    // Grouping by category with filtered invoices and payments
    $categoryWise = $projects->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary = $categoryWise->map(function ($group) use ($financialYears) {
        $allInvoices = $group->flatMap->invoices;
        $allPayments = $allInvoices->flatMap->payments;

        // Calculate year-wise budget and revenue with fallback
        $yearlyBudgetTotal = 0;
        $yearlyRevenueTotal = 0;

        // Initialize year-wise data structure
        $yearWiseData = [];
        foreach ($financialYears as $fy) {
            $yearWiseData[$fy->id] = ['budget' => 0, 'revenue' => 0];
        }

        // Find current financial year
        $today = Carbon::today();
        $currentFY = $financialYears->first(function($fy) use ($today) {
            return $today->between($fy->start_date, $fy->end_date);
        });

        foreach ($group as $project) {
            if ($project->yearlyBudgets->isNotEmpty()) {
                // Use year-wise budget and revenue
                $yearlyBudgetTotal += $project->yearlyBudgets->sum('amount');
                $yearlyRevenueTotal += $project->yearlyBudgets->sum('yearly_revenue');

                // Populate year-wise data
                foreach ($project->yearlyBudgets as $yb) {
                    if ($yb->financial_year_id && isset($yearWiseData[$yb->financial_year_id])) {
                        $yearWiseData[$yb->financial_year_id]['budget'] += $yb->amount;
                        $yearWiseData[$yb->financial_year_id]['revenue'] += $yb->yearly_revenue;
                    }
                }
            } else {
                // Fallback to project-level budget and revenue
                $yearlyBudgetTotal += $project->budget;
                $yearlyRevenueTotal += $project->revenue;

                if ($currentFY && isset($yearWiseData[$currentFY->id])) {
                    $yearWiseData[$currentFY->id]['budget'] += $project->budget;
                    $yearWiseData[$currentFY->id]['revenue'] += $project->revenue;
                }
            }
        }

        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'yearly_budget' => $yearlyBudgetTotal / 100000,
            'yearly_revenue' => $yearlyRevenueTotal / 100000,
            'year_wise_data' => collect($yearWiseData)->map(fn($data) => [
                'budget' => $data['budget'] / 100000,
                'revenue' => $data['revenue'] / 100000
            ])->toArray(),
            'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
            'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
            'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
            'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
            'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
            'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
            'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
            'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
            'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
            'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
             'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
            'yearly_financials' => $group->reduce(function ($carry, $project) {
                 foreach ($project->yearlyBudgets as $yb) {
                    $fy = $yb->financialYear->name ?? 'N/A';
                    if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                    $carry[$fy]['budget'] += $yb->budget_amount;
                }
                foreach ($project->expenseComponents as $ec) {
                    if ($ec->type == 1 && $ec->financial_year) {
                         $fy = $ec->financial_year;
                         if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                         $carry[$fy]['expense'] += $ec->amount;
                    }
                }
                ksort($carry);
                return $carry;
            }, [])
        ];
    });

    $investigatorWise = $projects->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator');

    $investigatorSummary = $investigatorWise->map(function ($group) use ($financialYears) {
        $allInvoices = $group->flatMap->invoices;
        $allPayments = $allInvoices->flatMap->payments;

        // Calculate year-wise budget and revenue with fallback
        $yearlyBudgetTotal = 0;
        $yearlyRevenueTotal = 0;

        // Initialize year-wise data structure
        $yearWiseData = [];
        foreach ($financialYears as $fy) {
            $yearWiseData[$fy->id] = ['budget' => 0, 'revenue' => 0];
        }

        // Find current financial year
        $today = Carbon::today();
        $currentFY = $financialYears->first(function($fy) use ($today) {
            return $today->between($fy->start_date, $fy->end_date);
        });

        foreach ($group as $project) {
            if ($project->yearlyBudgets->isNotEmpty()) {
                $yearlyBudgetTotal += $project->yearlyBudgets->sum('amount');
                $yearlyRevenueTotal += $project->yearlyBudgets->sum('yearly_revenue');

                // Populate year-wise data
                foreach ($project->yearlyBudgets as $yb) {
                    if ($yb->financial_year_id && isset($yearWiseData[$yb->financial_year_id])) {
                        $yearWiseData[$yb->financial_year_id]['budget'] += $yb->amount;
                        $yearWiseData[$yb->financial_year_id]['revenue'] += $yb->yearly_revenue;
                    }
                }
            } else {
                $yearlyBudgetTotal += $project->budget;
                $yearlyRevenueTotal += $project->revenue;

                if ($currentFY && isset($yearWiseData[$currentFY->id])) {
                    $yearWiseData[$currentFY->id]['budget'] += $project->budget;
                    $yearWiseData[$currentFY->id]['revenue'] += $project->revenue;
                }
            }
        }

        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'yearly_budget' => $yearlyBudgetTotal / 100000,
            'yearly_revenue' => $yearlyRevenueTotal / 100000,
            'year_wise_data' => collect($yearWiseData)->map(fn($data) => [
                'budget' => $data['budget'] / 100000,
                'revenue' => $data['revenue'] / 100000
            ])->toArray(),
            'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
            'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
            'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
            'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
            'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
            'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
            'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
            'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
            'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
            'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
             'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
            'yearly_financials' => $group->reduce(function ($carry, $project) {
                // Budget Fallback Logic
                if ($project->yearlyBudgets->isEmpty()) {
                     $fy = 'Others';
                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                     $carry[$fy]['budget'] += $project->budget;
                } else {
                    foreach ($project->yearlyBudgets as $yb) {
                        $fy = $yb->financialYear->name ?? 'N/A';
                        if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                        $carry[$fy]['budget'] += $yb->budget_amount;
                    }
                }

                // Expense Logic
                foreach ($project->expenseComponents as $ec) {
                    if ($ec->type == 1) {
                         $fy = $ec->financial_year ?? 'Others';
                         if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                         $carry[$fy]['expense'] += $ec->amount;
                    }
                }
                ksort($carry);
                // Move 'Others' to bottom if exists
                if (isset($carry['Others'])) {
                    $others = $carry['Others'];
                    unset($carry['Others']);
                    $carry['Others'] = $others;
                }
                return $carry;
            }, [])
        ];
    });

    $investigatorCategoryWise = $projects
        ->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator')
        ->map(function ($group) use ($financialYears) {
            return $group->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized')
                ->map(function ($catGroup) use ($financialYears) {
                    $allInvoices = $catGroup->flatMap->invoices;
                    $allPayments = $allInvoices->flatMap->payments;

                    // Calculate year-wise budget and revenue with fallback
                    $yearlyBudgetTotal = 0;
                    $yearlyRevenueTotal = 0;

                    // Initialize year-wise data structure
                    $yearWiseData = [];
                    foreach ($financialYears as $fy) {
                        $yearWiseData[$fy->id] = ['budget' => 0, 'revenue' => 0];
                    }

                    // Find current financial year
                    $today = Carbon::today();
                    $currentFY = $financialYears->first(function($fy) use ($today) {
                        return $today->between($fy->start_date, $fy->end_date);
                    });

                    foreach ($catGroup as $project) {
                        if ($project->yearlyBudgets->isNotEmpty()) {
                            $yearlyBudgetTotal += $project->yearlyBudgets->sum('amount');
                            $yearlyRevenueTotal += $project->yearlyBudgets->sum('yearly_revenue');

                            // Populate year-wise data
                            foreach ($project->yearlyBudgets as $yb) {
                                if ($yb->financial_year_id && isset($yearWiseData[$yb->financial_year_id])) {
                                    $yearWiseData[$yb->financial_year_id]['budget'] += $yb->amount;
                                    $yearWiseData[$yb->financial_year_id]['revenue'] += $yb->yearly_revenue;
                                }
                            }
                        } else {
                            $yearlyBudgetTotal += $project->budget;
                            $yearlyRevenueTotal += $project->revenue;

                            if ($currentFY && isset($yearWiseData[$currentFY->id])) {
                                $yearWiseData[$currentFY->id]['budget'] += $project->budget;
                                $yearWiseData[$currentFY->id]['revenue'] += $project->revenue;
                            }
                        }
                    }

                    return [
                        'total_budget' => $catGroup->sum('budget') / 100000,
                        'total_revenue' => $catGroup->sum('revenue') / 100000,
                        'yearly_budget' => $yearlyBudgetTotal / 100000,
                        'yearly_revenue' => $yearlyRevenueTotal / 100000,
                        'year_wise_data' => collect($yearWiseData)->map(fn($data) => [
                            'budget' => $data['budget'] / 100000,
                            'revenue' => $data['revenue'] / 100000
                        ])->toArray(),
                        'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
                        'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
                        'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
                        'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
                        'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
                        'ongoing_count' => $catGroup->where('status', Project::STATUS_ONGOING)->count(),
                        'completed_count' => $catGroup->where('status', Project::STATUS_COMPLETED)->count(),
                        'delayed_count' => $catGroup->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
                        'initiated_count' => $catGroup->where('status', Project::STATUS_INITIATED)->count(),
                        'archived_count' => $catGroup->where('status', Project::STATUS_ARCHIVED)->count(),
                         'total_expense' => $catGroup->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
                        'yearly_financials' => $catGroup->reduce(function ($carry, $project) {
                             foreach ($project->yearlyBudgets as $yb) {
                                $fy = $yb->financialYear->name ?? 'N/A';
                                if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                $carry[$fy]['budget'] += $yb->budget_amount;
                            }
                            foreach ($project->expenseComponents as $ec) {
                                if ($ec->type == 1 && $ec->financial_year) {
                                     $fy = $ec->financial_year;
                                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                     $carry[$fy]['expense'] += $ec->amount;
                                }
                            }
                            ksort($carry);
                            return $carry;
                        }, [])
                    ];
                });
        })
        ->sortByDesc(function ($categories, $investigator) {
            $totalRevenue = $categories->sum('total_revenue');
            $projectCount = $categories->sum(fn($c) =>
                ($c['ongoing_count'] ?? 0)
                + ($c['completed_count'] ?? 0)
                + ($c['initiated_count'] ?? 0)
            );
            return [$totalRevenue, $projectCount];
        });

    // Proposal submitted but no client status
    $proposalsPending = Proposal::with('requirement.category')
        ->whereNull('client_status')
        ->get();

    // Initiated stage requirements
    $initiatedRequirements = Requirement::with('category')
        ->where('proposal_status', 0)
        ->get();

    return view('pms.reports.project-status-detailed', [
        'projects' => $projects,
        'categoryWise' => $categoryWise,
        'categorySummary_proposalSubmitted' => $categorySummary_proposalSubmitted,
        'categorySummary_planningStage' => $categorySummary_planningStage,
        'categorySummary' => $categorySummary,
        'proposalsPending' => $proposalsPending,
        'initiatedRequirements' => $initiatedRequirements,
        'liveProjects' => $liveProjects,
        'ongoingProjects' => $ongoingProjects,
        'proposalSubmitted' => $proposalSubmitted,
        'planningStage' => $planningStage,
        'completedProjects' => $completedProjects,
        'delayedProjects' => $delayedProjects,
        'archived' => $archived,
        'investigatorSummary' => $investigatorSummary,
        'investigatorCategoryWise' => $investigatorCategoryWise,
        'financialYears' => $financialYears,
        'allFinancialYearsForFilter' => $allFinancialYearsForFilter,
        'selectedFinancialYearIds' => $selectedFinancialYearIds,
        'pageConfigs' => $pageConfigs
    ]);
}
public function projectStatusDetailedWithoutYearSplitup(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];

    $user = Auth::user();

    $categoryId = $request->input('category_id');
    if ($user->hasRole('director') || $user->hasRole('finance') || $user->hasRole('Project Report') || $user->hasRole('Project Investigator')) {
        $investigatorId = $request->input('investigator_id');
    } else {
        $investigatorId = $user->id;
    }
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $liveProjects = Project::where('status', Project::STATUS_ONGOING)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $completedProjects = Project::where('status', Project::STATUS_COMPLETED)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $ongoingProjects = Project::where('status', Project::STATUS_ONGOING)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $delayedProjects = Project::where('status', Project::STATUS_ONGOING)
        ->where('end_date', '<', Carbon::today())
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    $archived = Project::where('status', Project::STATUS_ARCHIVED)
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

    // Proposal Submitted
    $proposalSubmitted_query = Proposal::where('project_status', 0)
        ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('expected_start_date', [$startDate, $endDate]))
        ->where('project_status', 0)
        ->get();
    $proposalSubmitted = $proposalSubmitted_query->count();

    // Grouping by category
    $categoryWise_proposalSubmitted = $proposalSubmitted_query->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary_proposalSubmitted = $categoryWise_proposalSubmitted->map(function ($group) {
        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'Accepted' => $group->where('client_status', 'accepted')->count(),
            'Rejected' => $group->where('client_status', 'rejected')->count(),
            'resubmit_requested' => $group->where('client_status', 'resubmit_requested')->count(),
            'submitted' => $group->whereNull('client_status')->count(),
        ];
    });

    // Planning Stage
    $planningStage_query = Requirement::where('proposal_status', 0)
        ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
        ->when($categoryId, fn($q) => $q->where('project_category_id', $categoryId))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
        ->get();
    $planningStage = $planningStage_query->count();

    $categoryWise_planningStage = $planningStage_query->groupBy(fn($p) => $p->category->name ?? 'Uncategorized');

    $categorySummary_planningStage = $categoryWise_planningStage->map(function ($group) {
        return [
            'created' => $group->where('status', 0)->count(),
            'submitted' => $group->where('status', 1)->count(),
            'under_pac' => $group->where('status', 4)->count(),
            'rejected' => $group->where('status', 3)->count(),
            'approved' => $group->whereNotNull('allocated_to')->count(),
        ];
    });

    // Base query with relationships - Eager load invoices with date filter
    $projectsQuery = Project::with(['requirement.category', 'proposal', 'investigator', 'yearlyBudgets.financialYear', 'expenseComponents'])
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        ->whereIn('status', [Project::STATUS_ONGOING, Project::STATUS_COMPLETED])
        ->when($startDate && $endDate, fn($q) =>
            $q->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
        );

    // Load projects with filtered invoices and their payments
    $projects = $projectsQuery->get()->map(function ($project) use ($startDate, $endDate) {
        // Load invoices filtered by date
        $project->setRelation('invoices',
            $project->invoices()
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('invoice_date', [$startDate, $endDate]);
                })
                ->with('payments') // Load payments for the filtered invoices
                ->get()
        );
        return $project;
    });
    // print_r($projects->where('id',876));exit;

    // Grouping by category with filtered invoices and payments
    $categoryWise = $projects->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary = $categoryWise->map(function ($group) {
        $allInvoices = $group->flatMap->invoices;
        $allPayments = $allInvoices->flatMap->payments;

        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
            'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
            'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
            'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
            'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
            'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
            'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
            'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
            'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
            'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
             'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
            'yearly_financials' => $group->reduce(function ($carry, $project) {
                 foreach ($project->yearlyBudgets as $yb) {
                    $fy = $yb->financialYear->name ?? 'N/A';
                    if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                    $carry[$fy]['budget'] += $yb->budget_amount;
                }
                foreach ($project->expenseComponents as $ec) {
                    if ($ec->type == 1 && $ec->financial_year) {
                         $fy = $ec->financial_year;
                         if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                         $carry[$fy]['expense'] += $ec->amount;
                    }
                }
                ksort($carry);
                return $carry;
            }, [])
        ];
    });

    $investigatorWise = $projects->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator');

    $investigatorSummary = $investigatorWise->map(function ($group) {
        $allInvoices = $group->flatMap->invoices;
        $allPayments = $allInvoices->flatMap->payments;

        return [
            'total_budget' => $group->sum('budget') / 100000,
            'total_revenue' => $group->sum('revenue') / 100000,
            'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
            'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
            'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
            'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
            'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
            'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
            'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
            'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
            'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
            'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
             'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
            'yearly_financials' => $group->reduce(function ($carry, $project) {
                // Budget Fallback Logic
                if ($project->yearlyBudgets->isEmpty()) {
                     $fy = 'Others';
                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                     $carry[$fy]['budget'] += $project->budget;
                } else {
                    foreach ($project->yearlyBudgets as $yb) {
                        $fy = $yb->financialYear->name ?? 'N/A';
                        if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                        $carry[$fy]['budget'] += $yb->budget_amount;
                    }
                }

                // Expense Logic
                foreach ($project->expenseComponents as $ec) {
                    if ($ec->type == 1) {
                         $fy = $ec->financial_year ?? 'Others';
                         if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                         $carry[$fy]['expense'] += $ec->amount;
                    }
                }
                ksort($carry);
                // Move 'Others' to bottom if exists
                if (isset($carry['Others'])) {
                    $others = $carry['Others'];
                    unset($carry['Others']);
                    $carry['Others'] = $others;
                }
                return $carry;
            }, [])
        ];
    });

    $investigatorCategoryWise = $projects
        ->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator')
        ->map(function ($group) {
            return $group->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized')
                ->map(function ($catGroup) {
                    $allInvoices = $catGroup->flatMap->invoices;
                    $allPayments = $allInvoices->flatMap->payments;

                    return [
                        'total_budget' => $catGroup->sum('budget') / 100000,
                        'total_revenue' => $catGroup->sum('revenue') / 100000,
                        'total_invoice_raised' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') / 100000,
                        'total_invoice_raised_tax' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 2)->sum('total_amount') / 100000,
                        'total_invoice_raised_proforma' => $allInvoices->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->where('invoice_type', 1)->sum('total_amount') / 100000,
                        'total_invoice_paid' => $allPayments->sum('amount') / 100000, // Payments from filtered invoices only
                        'total_balance' => ($allInvoices->where('invoice_type', 2)->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])->sum('total_amount') - $allPayments->sum('amount')) / 100000,
                        'ongoing_count' => $catGroup->where('status', Project::STATUS_ONGOING)->count(),
                        'completed_count' => $catGroup->where('status', Project::STATUS_COMPLETED)->count(),
                        'delayed_count' => $catGroup->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
                        'initiated_count' => $catGroup->where('status', Project::STATUS_INITIATED)->count(),
                        'archived_count' => $catGroup->where('status', Project::STATUS_ARCHIVED)->count(),
                         'total_expense' => $catGroup->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
                        'yearly_financials' => $catGroup->reduce(function ($carry, $project) {
                             foreach ($project->yearlyBudgets as $yb) {
                                $fy = $yb->financialYear->name ?? 'N/A';
                                if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                $carry[$fy]['budget'] += $yb->budget_amount;
                            }
                            foreach ($project->expenseComponents as $ec) {
                                if ($ec->type == 1 && $ec->financial_year) {
                                     $fy = $ec->financial_year;
                                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                     $carry[$fy]['expense'] += $ec->amount;
                                }
                            }
                            ksort($carry);
                            return $carry;
                        }, [])
                    ];
                });
        })
        ->sortByDesc(function ($categories, $investigator) {
            $totalRevenue = $categories->sum('total_revenue');
            $projectCount = $categories->sum(fn($c) =>
                ($c['ongoing_count'] ?? 0)
                + ($c['completed_count'] ?? 0)
                + ($c['initiated_count'] ?? 0)
            );
            return [$totalRevenue, $projectCount];
        });

    // Proposal submitted but no client status
    $proposalsPending = Proposal::with('requirement.category')
        ->whereNull('client_status')
        ->get();

    // Initiated stage requirements
    $initiatedRequirements = Requirement::with('category')
        ->where('proposal_status', 0)
        ->get();

    return view('pms.reports.project-status-detailed', [
        'projects' => $projects,
        'categoryWise' => $categoryWise,
        'categorySummary_proposalSubmitted' => $categorySummary_proposalSubmitted,
        'categorySummary_planningStage' => $categorySummary_planningStage,
        'categorySummary' => $categorySummary,
        'proposalsPending' => $proposalsPending,
        'initiatedRequirements' => $initiatedRequirements,
        'liveProjects' => $liveProjects,
        'ongoingProjects' => $ongoingProjects,
        'proposalSubmitted' => $proposalSubmitted,
        'planningStage' => $planningStage,
        'completedProjects' => $completedProjects,
        'delayedProjects' => $delayedProjects,
        'archived' => $archived,
        'investigatorSummary' => $investigatorSummary,
        'investigatorCategoryWise' => $investigatorCategoryWise,
        'pageConfigs' => $pageConfigs
    ]);
}

public function projectStatusDetailedWithoutinvoice_adtefilter(Request $request)
{
  $pageConfigs = ['myLayout' => 'horizontal'];

  $user = Auth::user();

    $categoryId = $request->input('category_id');
       if ($user->hasRole('director') || $user->hasRole('finance') || $user->hasRole('Project Report') || $user->hasRole('Project Investigator')) {
    $investigatorId = $request->input('investigator_id');
       }
       else {
           $investigatorId =$user->id;
       }
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');


      $liveProjects = Project::where('status', Project::STATUS_ONGOING)
         ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
        ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

         $completedProjects = Project::where('status', Project::STATUS_COMPLETED)
         ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
        ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();
      $ongoingProjects = Project::where('status', Project::STATUS_ONGOING)
         ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
        ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();

        $delayedProjects = Project::where('status', Project::STATUS_ONGOING)
    ->where('end_date', '<', Carbon::today()) // ✅ ongoing projects with end_date in future
    ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
    // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
    ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
    ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
    ->count();

     $archived = Project::where('status', Project::STATUS_ARCHIVED)
         ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
        // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
        ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->count();


        // Proposal Submitted
        $proposalSubmitted_query = Proposal::where('project_status', 0)
         ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
         ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('expected_start_date', [$startDate, $endDate]))
            // ->whereNull('client_status')
            ->where('project_status',0)
            ->get();
             $proposalSubmitted =  $proposalSubmitted_query->count();

            // Grouping by category
    $categoryWise_proposalSubmitted = $proposalSubmitted_query->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary_proposalSubmitted = $categoryWise_proposalSubmitted->map(function ($group) {
        return [
            'total_budget' => $group->sum('budget')/100000,
            'total_revenue' => $group->sum('revenue')/100000,
            'Accepted' => $group->where('client_status', 'accepted')->count(),
            'Rejected' => $group->where('client_status', 'rejected')->count(),
            'resubmit_requested' => $group->where('client_status', 'resubmit_requested')->count(),
            'submitted' => $group->whereNull('client_status')->count(),
        ];
    });

        // Planning Stage
        $planningStage_query = Requirement::where('proposal_status', 0)
          ->when($investigatorId, fn($q) => $q->where('created_by', $investigatorId))
            ->when($categoryId, fn($q) => $q->where('project_category_id', $categoryId))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
        ->get();
          $planningStage = $planningStage_query->count();
      $categoryWise_planningStage = $planningStage_query->groupBy(fn($p) => $p->category->name ?? 'Uncategorized');

      $categorySummary_planningStage = $categoryWise_planningStage->map(function ($group) {
        return [
            'created' => $group->where('status', 0)->count(),
            'submitted' => $group->where('status', 1)->count(),
            'under_pac' => $group->where('status', 4)->count(),
            'rejected' => $group->where('status',3)->count(),
              'approved' => $group->whereNotNull('allocated_to')->count(),
        ];
    });

    // Base query with relationships
    $projects = Project::with(['requirement.category', 'proposal', 'invoices.payments', 'investigator'])
        ->when($categoryId, fn($q) => $q->whereHas('requirement', fn($r) => $r->where('project_category_id', $categoryId)))
        ->when($investigatorId, fn($q) => $q->where('project_investigator_id', $investigatorId))
           ->whereIn('status', [Project::STATUS_ONGOING,Project::STATUS_COMPLETED])
        // ->when($startDate && $endDate, fn($q) => $q->whereBetween('start_date', [$startDate, $endDate]))
        ->when($startDate && $endDate, fn($q) =>
        $q->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
    )
        ->get();

    // Grouping by category
    $categoryWise = $projects->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized');

    $categorySummary = $categoryWise->map(function ($group) {
        return [
            'total_budget' => $group->sum('budget')/100000,
            'total_revenue' => $group->sum('revenue')/100000,
            'total_invoice_raised' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount'))/100000,
            'total_invoice_raised_tax' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',2)->sum('total_amount'))/100000,
            'total_invoice_raised_proforma' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',1)->sum('total_amount'))/100000,
            'total_invoice_paid' => $group->sum(fn($p) => $p->invoices->sum(fn($i) => $i->payments->sum('amount')))/100000,
            'total_balance' => $group->sum(fn($p) => $p->invoices->where('invoice_type',2)->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount') - $p->invoices->sum(fn($i) => $i->payments->sum('amount')))/100000,
            'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
            'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
            'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
            'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
            'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
             'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
            'yearly_financials' => $group->reduce(function ($carry, $project) {
                if ($project->yearlyBudgets->isEmpty()) {
                     $fy = 'Others';
                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                     $carry[$fy]['budget'] += $project->budget;
                } else {
                    foreach ($project->yearlyBudgets as $yb) {
                        $fy = $yb->financialYear->name ?? 'N/A';
                        if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                        $carry[$fy]['budget'] += $yb->budget_amount;
                    }
                }
                foreach ($project->expenseComponents as $ec) {
                    if ($ec->type == 1) {
                         $fy = $ec->financial_year ?? 'Others';
                         if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                         $carry[$fy]['expense'] += $ec->amount;
                    }
                }
                ksort($carry);
                if (isset($carry['Others'])) {
                    $others = $carry['Others'];
                    unset($carry['Others']);
                    $carry['Others'] = $others;
                }
                return $carry;
            }, [])
        ];
    });


    $investigatorWise = $projects->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator');

$investigatorSummary = $investigatorWise->map(function ($group) {
    return [
        'total_budget' => $group->sum('budget') / 100000,
        'total_revenue' => $group->sum('revenue') / 100000,
        'total_invoice_raised' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount')) / 100000,
        'total_invoice_raised_tax' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',2)->sum('total_amount'))/100000,
        'total_invoice_raised_proforma' => $group->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',1)->sum('total_amount'))/100000,
        'total_invoice_paid' => $group->sum(fn($p) => $p->invoices->sum(fn($i) => $i->payments->sum('amount'))) / 100000,
        'total_balance' => $group->sum(fn($p) => $p->invoices->where('invoice_type',2)->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount') - $p->invoices->sum(fn($i) => $i->payments->sum('amount'))) / 100000,
        'ongoing_count' => $group->where('status', Project::STATUS_ONGOING)->count(),
        'completed_count' => $group->where('status', Project::STATUS_COMPLETED)->count(),
        'initiated_count' => $group->where('status', Project::STATUS_INITIATED)->count(),
         'archived_count' => $group->where('status', Project::STATUS_ARCHIVED)->count(),
        'delayed_count' => $group->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
         'total_expense' => $group->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
        'yearly_financials' => $group->reduce(function ($carry, $project) {
            if ($project->yearlyBudgets->isEmpty()) {
                 $fy = 'Others';
                 if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                 $carry[$fy]['budget'] += $project->budget;
            } else {
                foreach ($project->yearlyBudgets as $yb) {
                    $fy = $yb->financialYear->name ?? 'N/A';
                    if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                    $carry[$fy]['budget'] += $yb->budget_amount;
                }
            }
            foreach ($project->expenseComponents as $ec) {
                if ($ec->type == 1) {
                     $fy = $ec->financial_year ?? 'Others';
                     if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                     $carry[$fy]['expense'] += $ec->amount;
                }
            }
            ksort($carry);
            if (isset($carry['Others'])) {
                $others = $carry['Others'];
                unset($carry['Others']);
                $carry['Others'] = $others;
            }
            return $carry;
        }, [])
    ];
});

$investigatorCategoryWise = $projects
    ->groupBy(fn($p) => $p->investigator->name ?? 'No Investigator')
    ->map(function ($group) {
        return $group->groupBy(fn($p) => $p->requirement->category->name ?? 'Uncategorized')
            ->map(function ($catGroup) {
                return [
                    'total_budget' => $catGroup->sum('budget') / 100000,
                    'total_revenue' => $catGroup->sum('revenue') / 100000,
                    'total_invoice_raised' => $catGroup->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount')) / 100000,
                        'total_invoice_raised_tax' => $catGroup->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',2)->sum('total_amount'))/100000,
            'total_invoice_raised_proforma' => $catGroup->sum(fn($p) => $p->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',1)->sum('total_amount'))/100000,
                    'total_invoice_paid' => $catGroup->sum(fn($p) => $p->invoices->sum(fn($i) => $i->payments->sum('amount'))) / 100000,
                    'total_balance' => $catGroup->sum(fn($p) => $p->invoices->where('invoice_type',2)->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount') - $p->invoices->sum(fn($i) => $i->payments->sum('amount'))) / 100000,
                    'ongoing_count' => $catGroup->where('status', Project::STATUS_ONGOING)->count(),
                     'completed_count' => $catGroup->where('status', Project::STATUS_COMPLETED)->count(),
                    'delayed_count' => $catGroup->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count(),
                    'initiated_count' => $catGroup->where('status', Project::STATUS_INITIATED)->count(),
                    'initiated_count' => $catGroup->where('status', Project::STATUS_INITIATED)->count(),
                     'archived_count' => $catGroup->where('status', Project::STATUS_ARCHIVED)->count(),
                      'total_expense' => $catGroup->flatMap->expenseComponents->where('type', 1)->sum('amount') / 100000,
                     'yearly_financials' => $catGroup->reduce(function ($carry, $project) {
                         if ($project->yearlyBudgets->isEmpty()) {
                              $fy = 'Others';
                              if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                              $carry[$fy]['budget'] += $project->budget;
                         } else {
                             foreach ($project->yearlyBudgets as $yb) {
                                 $fy = $yb->financialYear->name ?? 'N/A';
                                 if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                 $carry[$fy]['budget'] += $yb->budget_amount;
                             }
                         }
                         foreach ($project->expenseComponents as $ec) {
                             if ($ec->type == 1) {
                                  $fy = $ec->financial_year ?? 'Others';
                                  if (!isset($carry[$fy])) $carry[$fy] = ['budget' => 0, 'expense' => 0];
                                  $carry[$fy]['expense'] += $ec->amount;
                             }
                         }
                         ksort($carry);
                         if (isset($carry['Others'])) {
                             $others = $carry['Others'];
                             unset($carry['Others']);
                             $carry['Others'] = $others;
                         }
                         return $carry;
                     }, [])
                ];
            });
    })
     // ✅ Sort investigators by total revenue, then project count
    ->sortByDesc(function ($categories, $investigator) {
        $totalRevenue = $categories->sum('total_revenue');
        $projectCount = $categories->sum(fn($c) =>
            ($c['ongoing_count'] ?? 0)
            + ($c['completed_count'] ?? 0)
            + ($c['initiated_count'] ?? 0)
            // + ($c['archived_count'] ?? 0)
        );
        return [$totalRevenue, $projectCount]; // Laravel compares arrays in order
    });

    // Proposal submitted but no client status
    $proposalsPending = Proposal::with('requirement.category')
        ->whereNull('client_status')
        ->get();

    // Initiated stage requirements
    $initiatedRequirements = Requirement::with('category')
        ->where('proposal_status', 0)
        ->get();

    return view('pms.reports.project-status-detailed', [
        'projects' => $projects,
        'categoryWise' => $categoryWise,
         'categorySummary_proposalSubmitted' => $categorySummary_proposalSubmitted,
          'categorySummary_planningStage' => $categorySummary_planningStage,
        'categorySummary' => $categorySummary,
        'proposalsPending' => $proposalsPending,
        'initiatedRequirements' => $initiatedRequirements,
         'liveProjects'      => $liveProjects,
          'ongoingProjects'      => $ongoingProjects,
            'proposalSubmitted' => $proposalSubmitted,
            'planningStage'     => $planningStage,
            'completedProjects' =>$completedProjects,
            'delayedProjects' =>$delayedProjects,
            'archived'=>$archived,
            'investigatorSummary' => $investigatorSummary,
            'investigatorCategoryWise' => $investigatorCategoryWise,
        'pageConfigs'=>$pageConfigs
    ]);
}



    public function financial(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $dateRange = $request->input('date_range', 'this_year');

        $query = Project::query();
        $query = $this->applyDateRangeFilter($query, $dateRange);

        $projects = $query->with(['invoices.payments'])->get();

        $financialData = [
            'total_budget' => $projects->sum('budget'),
            'total_revenue' => $projects->sum('revenue'),
            'total_expense' => $projects->sum('estimated_expense'),
            'total_invoiced' => $projects->sum(function($project) {
                return $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->sum('total_amount');
            }),
              'total_proforma_invoiced' => $projects->sum(function($project) {
                return $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',1)->sum('total_amount');
            }),
             'total_tax_invoiced' => $projects->sum(function($project) {
                return $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])->where('invoice_type',2)->sum('total_amount');
            }),
            'total_paid' => $projects->sum(function($project) {
                return $project->invoices->sum(function($invoice) {
                    return $invoice->payments->sum('amount');
                });
            }),
            'total_pending' => $projects->sum(function($project) {
                return $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->sum('total_amount') - $project->invoices->sum(function($invoice) {
                    return $invoice->payments->sum('amount');
                });
            }),
        ];

        $dateRanges = [
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            'last_week' => 'Last Week',
            'last_month' => 'Last Month',
            'last_quarter' => 'Last Quarter',
            'last_year' => 'Last Year',
            'custom' => 'Custom Range'
        ];

        return view('pms.reports.financial', compact('financialData', 'dateRanges', 'dateRange'),['pageConfigs'=> $pageConfigs]);
    }

    public function timesheet(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $userId = $request->input('user_id', 'all');
        $dateRange = $request->input('date_range', 'this_month');

        $query = Timesheet::query();

        // Apply user filter
        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        // Apply date range filter
        $query = $this->applyDateRangeFilter($query, $dateRange);

        $timesheets = $query->with(['user', 'category', 'project'])->get();

        $groupedTimesheets = $timesheets->groupBy(['user_id', 'category_id']);

        $users = User::whereHas('timesheets')->get();

        $dateRanges = [
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            'last_week' => 'Last Week',
            'last_month' => 'Last Month',
            'last_quarter' => 'Last Quarter',
            'last_year' => 'Last Year',
            'custom' => 'Custom Range'
        ];

        return view('pms.reports.timesheet', compact('groupedTimesheets', 'users', 'userId', 'dateRanges', 'dateRange'),['pageConfigs'=> $pageConfigs]);
    }

    public function resourceUtilizationOldWorking(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $dateRange = $request->input('date_range', 'this_month');

        $query = Timesheet::query();
        if (!auth()->user()->hasRole('director')) {
          $query->where('user_id', auth()->id());
        }
        $query = $this->applyDateRangeFilter($query, $dateRange);

        $timesheets = $query->with(['user', 'project'])->get();

        // Calculate utilization per user
        $workingDays = $this->getWorkingDaysCount($dateRange);
        $totalAvailableHours = $workingDays * 8; // Assuming 8 hours per day

        $utilizationData = [];

        foreach ($timesheets->groupBy('user_id') as $userId => $userTimesheets) {
            $user = $userTimesheets->first()->user;
            $totalHours = $userTimesheets->sum('hours');
            $utilizationPercentage = ($totalHours / $totalAvailableHours) * 100;

            $utilizationData[] = [
                'user' => $user,
                'total_hours' => $totalHours,
                'utilization_percentage' => $utilizationPercentage,
                // 'projects' => $userTimesheets->groupBy('project_id')->map(function($projectTimesheets) {
                //     return [
                //         'project' => $projectTimesheets->first()->project,
                //         'hours' => $projectTimesheets->sum('hours')
                //     ];
                // })
                'projects' => $userTimesheets
                    ->groupBy(function ($item) {
                        return $item->project_id . '-' . $item->category_id;
                    })
                    ->map(function ($groupedTimesheets) {
                        $first = $groupedTimesheets->first();

                        return [
                            'project' => $first->project,        // May be null
                            'category' => $first->category,      // Always present
                            'hours' => $groupedTimesheets->sum('hours'),
                        ];
                    }),
            ];
        }

        $dateRanges = [
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            'last_week' => 'Last Week',
            'last_month' => 'Last Month',
            'last_quarter' => 'Last Quarter',
            'last_year' => 'Last Year',
            // 'custom' => 'Custom Range'
        ];

        return view('pms.reports.resource-utilization', compact('utilizationData', 'dateRanges', 'dateRange', 'workingDays'),['pageConfigs'=> $pageConfigs]);
    }
public function resourceUtilization1(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];
    $dateRange = $request->input('date_range', 'this_month');

    $query = Timesheet::with(['user', 'project', 'category', 'items']);

    if (!auth()->user()->hasRole('director')) {
        $query->where('user_id', auth()->id());
    }

    $query = $this->applyDateRangeFilter($query, $dateRange);
    $timesheets = $query->get();

    // Calculate utilization
    $workingDays = $this->getWorkingDaysCount($dateRange);
    $totalAvailableHours = $workingDays * 8;

    $utilizationData = [];

    foreach ($timesheets->groupBy('user_id') as $userId => $userTimesheets) {
        $user = $userTimesheets->first()->user;
        $totalHours = $userTimesheets->sum('hours');
        $utilizationPercentage = ($totalHours / $totalAvailableHours) * 100;

        // Group timesheets by project-category combination
        $projects = $userTimesheets
            ->groupBy(fn($t) => ($t->project_id ?? 'none') . '-' . ($t->category_id ?? 'none'))
            ->map(function ($grouped) {
                $first = $grouped->first();
                $categoryName = strtolower($first->category->name ?? '');

                // Handle "Others" category with sub-items
                if ($categoryName === 'others') {
                    $items = $grouped->flatMap->items;

                    return [
                        'project' => $first->project,
                        'category' => $first->category,
                        'hours' => $items->sum('hours') ?: $grouped->sum('hours'),
                        'items' => $items->map(function ($item) {
                            return (object)[
                                'description' => $item->description,
                                  'item_name' => $item->item_name,
                                'hours' => $item->hours,
                            ];
                        })
                    ];
                }

                // Regular project/category entry
                return [
                    'project' => $first->project,
                    'category' => $first->category,
                    'hours' => $grouped->sum('hours'),
                    'items' => collect(), // empty
                ];
            })
            ->values();

        $utilizationData[] = [
            'user' => $user,
            'total_hours' => $totalHours,
            'utilization_percentage' => $utilizationPercentage,
            'projects' => $projects,
        ];
    }

    $dateRanges = [
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_quarter' => 'This Quarter',
        'this_year' => 'This Year',
        'last_week' => 'Last Week',
        'last_month' => 'Last Month',
        'last_quarter' => 'Last Quarter',
        'last_year' => 'Last Year',
    ];

    return view('pms.reports.resource-utilization', compact(
        'utilizationData',
        'dateRanges',
        'dateRange',
        'workingDays'
    ), ['pageConfigs' => $pageConfigs]);
}

public function resourceUtilization(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];

    // Get date range parameters
    $dateRange = $request->input('date_range', 'this_month');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
     // Get selected user filter (single or multiple user IDs)
    $filterUser = $request->input('user_id', []);
        // Build list of users for filter dropdown (only for directors)
    $authUser = auth()->user();

if ($authUser->hasRole('director')) {

    // Director can see all active users
    $users = User::where('active', 1)
        ->orderBy('name')
        ->get();

} else {

    $users = collect();

    /** ------------------------------
     *  CASE 1: Project Investigator
     * ------------------------------ */
    // if ($authUser->hasRole('Project Investigator')) {

    //     $projectUserIds = Project::where('project_investigator_id', $authUser->id)
    //         ->with('teamMembers') // relation: project → teamMembers
    //         ->get()
    //         ->flatMap(fn($p) => $p->teamMembers->pluck('user_id'))
    //         ->unique();

    //     $users = $users->merge(
    //         User::whereIn('id', $projectUserIds)
    //             ->where('active', 1)
    //             ->get()
    //     );
    // }

    /** ------------------------------
     *  CASE 2: Leave Approver (Reporting Officer)
     *  Get employees whose reporting_officer == auth user
     * ------------------------------ */
    if ($authUser->hasRole('Leave Approver')) {

        $reporteeUserIds = User::whereHas('employee', function ($q) use ($authUser) {
            $q->where('reporting_officer', $authUser->id);
        })->pluck('id');

        $users = $users->merge(
            User::whereIn('id', $reporteeUserIds)
                ->where('active', 1)
                ->get()
        );
    }

    /** ------------------------------
     *  CASE 3: If both empty → fallback
     * ------------------------------ */
    if ($users->isEmpty()) {
        $users = collect([$authUser]);
    }

    // Remove duplicates & sort
    $users = $users->unique('id')->sortBy('name')->values();
}

    $query = Timesheet::with(['user', 'project', 'category', 'items']);

    // if (!auth()->user()->hasRole('director')) {
    //     $query->where('user_id', auth()->id());
    // }
    // if (auth()->user()->hasRole('director')) {
    //     if (!empty($filterUser)) {
    //         // If user selects one or more users
    //         $query->whereIn('user_id', (array)$filterUser);
    //     }
    // }
    // else {
    //     // Normal users → only their own data
    //     $query->where('user_id', auth()->id());
    // }

    if (!empty($filterUser)) {
            // If user selects one or more users
            $query->whereIn('user_id', (array)$filterUser);
        }
        else{
          if(!$authUser->hasRole('director')){
             $query->where('user_id', auth()->id());
          }

        }

    $query = $this->applyDateRangeFilterTimesheet($query, $dateRange, $startDate, $endDate);
    $timesheets = $query->get();

    // Calculate working days and period info
    $periodInfo = $this->getPeriodInfo($dateRange, $startDate, $endDate);
    $workingDays = $periodInfo['working_days'];
    $totalAvailableHours = $workingDays * 8;

    $utilizationData = [];

    foreach ($timesheets->groupBy('user_id') as $userId => $userTimesheets) {
        $user = $userTimesheets->first()->user;
        $totalHours = $userTimesheets->sum('hours');
        $utilizationPercentage = ($totalHours / $totalAvailableHours) * 100;

        // Group timesheets by project-category combination
        $projects = $userTimesheets
            ->groupBy(fn($t) => ($t->project_id ?? 'none') . '-' . ($t->category_id ?? 'none'))
            ->map(function ($grouped) {
                $first = $grouped->first();
                $categoryName = strtolower($first->category->name ?? '');

                // Handle "Others" category with sub-items
                if ($categoryName === 'others') {
                    $items = $grouped->flatMap->items;

                    return [
                        'project' => $first->project,
                        'category' => $first->category,
                        'hours' => $items->sum('hours') ?: $grouped->sum('hours'),
                        'items' => $items->map(function ($item) {
                            return (object)[
                                'description' => $item->description,
                                'item_name' => $item->item_name,
                                'hours' => $item->hours,
                            ];
                        })
                    ];
                }

                // Regular project/category entry
                return [
                    'project' => $first->project,
                    'category' => $first->category,
                    'hours' => $grouped->sum('hours'),
                    'items' => collect(), // empty
                ];
            })
            ->values();

        $utilizationData[] = [
            'user' => $user,
            'total_hours' => $totalHours,
            'utilization_percentage' => $utilizationPercentage,
            'projects' => $projects,
        ];
    }

    $dateRanges = [
       'today' => 'Today',
        'yesterday' => 'Yesterday',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_quarter' => 'This Quarter',
        'this_year' => 'This Year',
        'last_week' => 'Last Week',
        'last_month' => 'Last Month',
        'last_quarter' => 'Last Quarter',
        'last_year' => 'Last Year',
        'custom' => 'Custom Range',
    ];

    return view('pms.reports.resource-utilization', compact(
        'utilizationData',
        'dateRanges',
        'dateRange',
        'workingDays',
        'periodInfo',
        'startDate',
        'endDate',
        'users',      // ADD THIS
        'filterUser'
    ), ['pageConfigs' => $pageConfigs]);
}

private function applyDateRangeFilterTimesheet($query, $dateRange, $startDate = null, $endDate = null)
{
    switch ($dateRange) {
      case 'today':
            $query->whereDate('date', Carbon::today());
            break;
        case 'yesterday':
            $query->whereDate('date', Carbon::yesterday());
            break;
        case 'this_week':
            $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            break;
        case 'this_month':
            $query->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            break;
        case 'this_quarter':
            $query->whereBetween('date', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
            break;
        case 'this_year':
            $query->whereBetween('date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
            break;
        case 'last_week':
            $query->whereBetween('date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
            break;
        case 'last_month':
            $query->whereBetween('date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
            break;
        case 'last_quarter':
            $query->whereBetween('date', [Carbon::now()->subQuarter()->startOfQuarter(), Carbon::now()->subQuarter()->endOfQuarter()]);
            break;
        case 'last_year':
            $query->whereBetween('date', [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()]);
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $query->whereBetween('date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }
            break;
    }

    return $query;
}

private function getPeriodInfo($dateRange, $startDate = null, $endDate = null)
{
    $start = null;
    $end = null;
    $holidays = DB::table('holidays')->pluck('date')->map(function ($date) {
        return Carbon::parse($date)->format('Y-m-d');
    })->toArray();

    switch ($dateRange) {
        case 'today':
            $start = Carbon::today();
            $end = Carbon::today();
            break;
        case 'yesterday':
            $start = Carbon::yesterday();
            $end = Carbon::yesterday();
            break;
        case 'this_week':
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
            break;
        case 'this_month':
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            break;
        case 'this_quarter':
            $start = Carbon::now()->startOfQuarter();
            $end = Carbon::now()->endOfQuarter();
            break;
        case 'this_year':
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now()->endOfYear();
            break;
        case 'last_week':
            $start = Carbon::now()->subWeek()->startOfWeek();
            $end = Carbon::now()->subWeek()->endOfWeek();
            break;
        case 'last_month':
            $start = Carbon::now()->subMonth()->startOfMonth();
            $end = Carbon::now()->subMonth()->endOfMonth();
            break;
        case 'last_quarter':
            $start = Carbon::now()->subQuarter()->startOfQuarter();
            $end = Carbon::now()->subQuarter()->endOfQuarter();
            break;
        case 'last_year':
            $start = Carbon::now()->subYear()->startOfYear();
            $end = Carbon::now()->subYear()->endOfYear();
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                // Default to current month if no custom dates provided
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
            }
            break;
        default:
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            break;
    }

    // Calculate working days excluding weekends and holidays
    $workingDays = $this->calculateWorkingDays($start, $end, $holidays);
    $totalDays = $start->diffInDays($end) + 1;
    $holidayCount = $this->countHolidays($start, $end, $holidays);

    return [
        'start_date' => $start->format('Y-m-d'),
        'end_date' => $end->format('Y-m-d'),
        'working_days' => $workingDays,
        'total_days' => $totalDays,
        'holiday_count' => $holidayCount,
        'period_string' => $start->format('M d, Y') . ' to ' . $end->format('M d, Y')
    ];
}

private function calculateWorkingDays($start, $end, $holidays)
{
    $workingDays = 0;
    $current = $start->copy();

    while ($current <= $end) {
        // Check if it's a weekend (Saturday = 6, Sunday = 0)
        if (!$current->isWeekend()) {
            // Check if it's not a holiday
            if (!in_array($current->format('Y-m-d'), $holidays)) {
                $workingDays++;
            }
        }
        $current->addDay();
    }

    return $workingDays;
}

private function countHolidays($start, $end, $holidays)
{
    $holidayCount = 0;
    $current = $start->copy();

    while ($current <= $end) {
        if (in_array($current->format('Y-m-d'), $holidays)) {
            $holidayCount++;
        }
        $current->addDay();
    }

    return $holidayCount;
}

    public function export($type, Request $request)
    {
        // Implement export functionality based on $type (excel, pdf, csv)
        // You can use Laravel Excel package for this
        // This is a placeholder implementation

        return redirect()->back()
            ->with('success', 'Export functionality will be implemented soon.');
    }

    private function applyDateRangeFilter($query, $dateRange)
    {
        switch ($dateRange) {
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'this_quarter':
                $query->whereBetween('created_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
                break;
            case 'this_year':
                $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
                break;
            case 'last_week':
                $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                break;
            case 'last_month':
                $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'last_quarter':
                $query->whereBetween('created_at', [Carbon::now()->subQuarter()->startOfQuarter(), Carbon::now()->subQuarter()->endOfQuarter()]);
                break;
            case 'last_year':
                $query->whereBetween('created_at', [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()]);
                break;
            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                }
                break;
        }

        return $query;
    }

    private function getWorkingDaysCount($dateRange)
    {
        // This is a simplified version - you might want to account for holidays
        switch ($dateRange) {
            case 'this_week':
            case 'last_week':
                return 5;
            case 'this_month':
            case 'last_month':
                return 20; // Approximate
            case 'this_quarter':
            case 'last_quarter':
                return 60; // Approximate
            case 'this_year':
            case 'last_year':
                return 240; // Approximate
            default:
                return 20; // Default for custom range
        }
    }



    public function projectDetailedReport1(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();

    // Filters
    $status = $request->input('status', 'all');
    $client = $request->input('client', 'all');
    $investigator = $request->input('investigator', 'all');
    $category = $request->input('category', 'all');
    $dateRange = $request->input('date_range', 'this_year');

    $query = Project::with([
        'requirement.client',
        'requirement.category',
        'investigator',
        'milestones.tasks',
        'invoices.payments',
        'expenses',
        'teamMembers.user'
    ]);

    // Role restriction
    if (!$user->hasRole('director') && !$user->hasRole('finance')) {
        $query->where('project_investigator_id', $user->id);
    }

    // Filters
    if ($status !== 'all') {
        $query->where('status', $status);
    }
    if ($client !== 'all') {
        $query->whereHas('requirement.client', function ($q) use ($client) {
            $q->where('id', $client);
        });
    }
    if ($investigator !== 'all') {
        $query->where('project_investigator_id', $investigator);
    }
    if ($category !== 'all') {
        $query->whereHas('requirement.category', function ($q) use ($category) {
            $q->where('id', $category);
        });
    }

    // Date range filter
    $query = $this->applyDateRangeFilter($query, $dateRange);

    $projects = $query->get();

    // Dropdown data
    $statuses = [
        'all' => 'All Statuses',
        0 => 'Initiated',
        1 => 'Ongoing',
        2 => 'Completed',
        3 => 'Archived',
    ];

    $clients = Client::select('id', 'client_name')->get();
    // $investigators = User::role('investigator')->select('id', 'name')->get();
    $investigators = User::whereHas('employee', function($q) {
$q->whereIn('designation', [2, 7, 9]);
    })->get();
    $categories = ProjectCategory::select('id', 'name')->get();

    // Totals
    $totals = [
        'budget' => $projects->sum('budget'),
        'estimated_expense' => $projects->sum('estimated_expense'),
        'actual_expense' => $projects->sum(fn($p) => $p->expenses->sum('total_amount')),
        'invoiced' => $projects->sum(fn($p) => $p->invoices->sum('total_amount')),
        'paid' => $projects->sum(fn($p) => $p->invoices->sum(fn($i) => $i->payments->sum('amount'))),
    ];
    $totals['balance'] = $totals['invoiced'] - $totals['paid'];

    return view('pms.reports.project-status-report', compact(
        'projects', 'statuses', 'clients', 'investigators', 'categories',
        'status', 'client', 'investigator', 'category', 'dateRange', 'totals'
    ), ['pageConfigs' => $pageConfigs]);
}

   public function projectDetailedReport(Request $request)
{

  $pageConfigs = ['myLayout' => 'horizontal'];
//  $investigators = User::whereHas('projects')->orderBy('name')->get();
//     $investigators = User::whereHas('employee', function($q) {
// $q->whereIn('designation', [2, 7, 9]);
//     })->get();

    $investigators = \App\Models\User::role('Project Investigator')
->with('employee')
->get();

    $clients = Client::orderBy('client_name')->get();
  $user = Auth::user();


    // Base query with relations
    $query = Project::with([
      'requirement',
        'requirement.client',
        'investigator',
        'milestones.tasks',
        'invoices.payments',
        'expenses',
        'teamMembers.user'
    ]);

     if ($user->hasRole('director') || $user->hasRole('finance') || $user->hasRole('Project Report')) {

            // $investigatorId =$user->id;
            //    $query->where('project_investigator_id', $investigatorId);
            if ($request->filled('investigator_id')) {
                $query->where('project_investigator_id', $request->investigator_id);
             }
    }
      else{
            $investigatorId =$user->id;
            $query->where('project_investigator_id', $investigatorId);
      }


    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }



    if ($request->filled('client_id')) {
        $query->whereHas('requirement', function ($q) use ($request) {
            $q->where('client_id', $request->client_id);
        });
    }

    $projects = $query->get();

    // Calculate stats
    $projects->map(function ($project) {
        $budget = $project->budget ?? 0;
        $estimated_expense=$project->estimated_expense ?? 0;
        $expenses = $project->expenses->sum('total_amount');
        $budgetRemaining = $budget - $expenses;
        $budgetUtilization = $budget > 0 ? ($expenses / $budget) * 100 : 0;

        $totalInvoiced = $project->invoices
            ->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])
            ->sum('total_amount');
             $totalInvoiced_proforma = $project->invoices
             ->where('invoice_type',1)
            ->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])
            ->sum('total_amount');
             $totalInvoiced_tax = $project->invoices
             ->where('invoice_type',2)
            ->whereIn('status', [\App\Models\PMS\Invoice::STATUS_SENT, \App\Models\PMS\Invoice::STATUS_PAID, \App\Models\PMS\Invoice::STATUS_OVERDUE])
            ->sum('total_amount');

        $totalPaid = $project->invoices->sum(function ($invoice) {
            return $invoice->payments->sum('amount');
        });

        $project->calculated = [
            'budget' => $budget,
            'estimated_expense'=>$estimated_expense,
            'expenses' => $expenses,
            'budget_remaining' => $budgetRemaining,
            'budget_utilization' => round($budgetUtilization, 2),
            'total_invoiced' => $totalInvoiced,
              'total_invoiced_proforma' => $totalInvoiced_proforma,
                'total_invoiced_tax' => $totalInvoiced_tax,
            'total_paid' => $totalPaid,
            'outstanding' => $totalInvoiced_tax - $totalPaid,
        ];

        return $project;
    });

    return view('pms.reports.project-status-report', compact(
        'projects',
        'pageConfigs',
        'investigators',
        'clients',
        'request'
    ));

    // return view('pms.reports.project-status-report', compact('projects', 'pageConfigs'));
}
}