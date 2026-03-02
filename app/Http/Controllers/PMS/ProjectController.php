<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\ProjectStoreRequest;
use App\Http\Requests\PMS\ProjectUpdateRequest;
use App\Models\PMS\Proposal;
use App\Models\PMS\Project;
use APP\Models\PMS\Invoice;
use App\Models\PMS\Requirement;
use App\Models\PMS\ProjectExpenseComponent;
use App\Models\PMS\FinancialYear;
use App\Models\ProjectCategory;
use App\Models\PMS\ExpenseCategory;
use App\Models\PMS\ProposalExpenseComponent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
         $user = Auth::user();
        $projects = Project::with(['requirement', 'proposal', 'investigator'])
        ->where(function ($query) use ($user) {
            $query->where('project_investigator_id', $user->id) // Investigator
                  ->orWhereHas('teamMembers', function ($q) use ($user) { // Team Member
                      $q->where('user_id', $user->id);
                  });

            // Role check
            if ($user->hasRole('director')) {
                $query->orWhereRaw('1 = 1'); // Include all projects for director
            }
        })
        ->where('status',0)
            ->latest()
            ->paginate(20);
            $projects_ongoing = Project::with(['requirement', 'proposal', 'investigator'])
        ->where(function ($query) use ($user) {
            $query->where('project_investigator_id', $user->id) // Investigator
                  ->orWhereHas('teamMembers', function ($q) use ($user) { // Team Member
                      $q->where('user_id', $user->id);
                  });

            // Role check
            if ($user->hasRole('director')) {
                $query->orWhereRaw('1 = 1'); // Include all projects for director
            }
        })
        ->where('status',1)
            // ->latest()
            // ->paginate(20);
            ->get();

            $projects_completed = Project::with(['requirement', 'proposal', 'investigator'])
        ->where(function ($query) use ($user) {
            $query->where('project_investigator_id', $user->id) // Investigator
                  ->orWhereHas('teamMembers', function ($q) use ($user) { // Team Member
                      $q->where('user_id', $user->id);
                  });

            // Role check
            if ($user->hasRole('director')) {
                $query->orWhereRaw('1 = 1'); // Include all projects for director
            }
        })
        ->where('status',2)
            ->latest()
            ->paginate(20);

            $projects_archived = Project::with(['requirement', 'proposal', 'investigator'])
        ->where(function ($query) use ($user) {
            $query->where('project_investigator_id', $user->id) // Investigator
                  ->orWhereHas('teamMembers', function ($q) use ($user) { // Team Member
                      $q->where('user_id', $user->id);
                  });

            // Role check
            if ($user->hasRole('director')) {
                $query->orWhereRaw('1 = 1'); // Include all projects for director
            }
        })
        ->where('status',3)
            ->latest()
            ->paginate(20);

        // return view('pms.projects.index', compact('projects'),['pageConfigs'=> $pageConfigs]);
         return view('pms.projects.project-management', compact('projects','projects_ongoing','projects_completed','projects_archived'),['pageConfigs'=> $pageConfigs]);
    }

    // public function create(Proposal $proposal)
    // {
    //     if ($proposal->status != Proposal::STATUS_APPROVED_BY_DIRECTOR ||
    //         !$proposal->client_accepted ||
    //         !$proposal->workOrderDocuments()->exists()) {
    //         return redirect()->route('pms.proposals.show', $proposal->id)
    //             ->with('error', 'Project cannot be created for this proposal. Ensure client has accepted and work order is uploaded.');
    //     }

    //     $categories = ProjectCategory::all();
    //     $faculty = User::whereHas('roles', function($q) {
    //         $q->where('name', 'faculty');
    //     })->get();

    //     return view('pms.projects.create', compact('proposal', 'categories', 'faculty'));
    // }

    public function createOld(Proposal $proposal)
{
    $pageConfigs = ['myLayout' => 'horizontal'];
    if ($proposal->client_status != 'accepted' || !$proposal->workOrderDocuments()->exists()) {
        return redirect()->route('pms.proposals.show', $proposal->id)
            ->with('error', 'Project cannot be created for this proposal. Ensure client has accepted and work order is uploaded.');
    }

    $categories = ProjectCategory::all();
    $faculty = User::whereHas('roles', function($q) {
        $q->where('name', 'faculty');
    })->get();
     $expenseCategories = ExpenseCategory::all();

     $financialYears = FinancialYear::getForProjectPeriod(
        $proposal->expected_start_date,
        $proposal->expected_end_date
    );

    return view('pms.projects.create', compact('proposal', 'categories', 'faculty','expenseCategories',  'financialYears'),['pageConfigs'=> $pageConfigs]);
}
    public function create(Proposal $proposal)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if ($proposal->client_status != 'accepted' || !$proposal->workOrderDocuments()->exists()) {
            return redirect()->route('pms.proposals.show', $proposal->id)
                ->with('error', 'Project cannot be created for this proposal. Ensure client has accepted and work order is uploaded.');
        }

        $categories = ProjectCategory::all();
        $faculty = User::whereHas('roles', fn($q) => $q->where('name', 'faculty'))->get();
        $expenseCategories = ExpenseCategory::all();

        // Get Financial Years covering the project period
        $financialYears = FinancialYear::getForProjectPeriod(
            $proposal->expected_start_date,
            $proposal->expected_end_date
        );

        // Prepare Proposal Data for JS
        $proposalData = [
            'id' => $proposal->id,
            'budget' => $proposal->budget,
            'estimated_expense' => $proposal->estimated_expense,
            'start_date' => $proposal->expected_start_date ? $proposal->expected_start_date->format('Y-m-d') : '',
            'end_date' => $proposal->expected_end_date ? $proposal->expected_end_date->format('Y-m-d') : '',
            'components' => [
                'estimated' => $proposal->expenseComponents()->where('type', ProposalExpenseComponent::TYPE_ESTIMATED)->get()->map(function($c) {
                    return [
                        'group' => $c->group_name,
                        'component' => $c->component,
                        'rate' => $c->rate,
                        'mandays' => $c->mandays,
                        'amount' => $c->amount,
                        'category_id' => $c->expense_category_id
                    ];
                }),
                'budgeted' => $proposal->expenseComponents()->where('type', ProposalExpenseComponent::TYPE_BUDGETED)->get()->map(function($c) {
                    return [
                        'group' => $c->group_name,
                        'component' => $c->component,
                        'rate' => $c->rate,
                        'mandays' => $c->mandays,
                        'amount' => $c->amount,
                        'category_id' => $c->expense_category_id
                    ];
                })
            ]
        ];

        return view('pms.projects.create', compact(
            'proposal',
            'categories',
            'faculty',
            'expenseCategories',
            'financialYears',
            'proposalData'
        ), ['pageConfigs' => $pageConfigs]);
    }

    // public function store(ProjectStoreRequest $request, Proposal $proposal)
    // {
    //     if ($proposal->status != Proposal::STATUS_APPROVED_BY_DIRECTOR ||
    //         !$proposal->client_accepted ||
    //         !$proposal->workOrderDocuments()->exists()) {
    //         return redirect()->route('pms.proposals.show', $proposal->id)
    //             ->with('error', 'Project cannot be created for this proposal. Ensure client has accepted and work order is uploaded.');
    //     }

    //     // Generate project code
    //     $clientCode = Str::upper(Str::substr($proposal->requirement->client->client_name, 0, 3));
    //     $categoryCode = $proposal->requirement->category->code;
    //     $year = now()->format('Y');
    //     $serial = Project::whereYear('created_at', $year)->count() + 1;

    //     $projectCode = "{$clientCode}/{$categoryCode}/{$year}/{$serial}";

    //     $data = $request->validated();
    //     $data['project_code'] = $projectCode;
    //     $data['requirement_id'] = $proposal->requirement_id;
    //     $data['proposal_id'] = $proposal->id;
    //     $data['status'] = Project::STATUS_INITIATED;

    //     $project = Project::create($data);

    //     // Add team members
    //     if ($request->has('team_members')) {
    //         foreach ($request->team_members as $memberId) {
    //             $project->teamMembers()->create([
    //                 'user_id' => $memberId,
    //                 'role' => 'member',
    //             ]);
    //         }
    //     }

    //     // Add project investigator as lead
    //     $project->teamMembers()->create([
    //         'user_id' => $request->project_investigator_id,
    //         'role' => 'lead',
    //     ]);

    //     // Copy documents from requirement and proposal to project
    //     $this->copyDocumentsToProject($project, $proposal);

    //     return redirect()->route('pms.projects.show', $project->id)
    //         ->with('success', 'Project created successfully.');
    // }

    public function storeOld(ProjectStoreRequest $request, Proposal $proposal)
{
    if ($proposal->client_status != 'accepted' || !$proposal->workOrderDocuments()->exists()) {
        return redirect()->route('pms.proposals.show', $proposal->id)
            ->with('error', 'Project cannot be created for this proposal. Ensure client has accepted and work order is uploaded.');
    }

    // Generate project code
    // $clientCode = Str::upper(Str::substr($proposal->requirement->client->client_name, 0, 3));
    // $categoryCode = $proposal->requirement->category->code;
    // $year = now()->format('Y');
    // $serial = Project::whereYear('created_at', $year)->count() + 1;

    // $projectCode = "{$clientCode}/{$categoryCode}/{$year}/{$serial}";
    $requirement = $proposal->requirement;
    $clientCode = $requirement->client->code ?? 'CLNT';
    $categoryCode = $requirement->category->code ?? 'CAT';
    $year = $proposal->expected_start_date
        ? $proposal->expected_start_date->format('y')
        : now()->format('y');

        $lastProject = Project::whereHas('requirement.client', fn($q) => $q->where('code', $clientCode))
        ->whereHas('requirement.category', fn($q) => $q->where('code', $categoryCode))
        ->whereYear('start_date', '20' . $year)
        ->orderBy('id', 'desc')
        ->first();

    if ($lastProject && preg_match('/(\d+)$/', $lastProject->project_code, $matches)) {
        $lastSequence = (int) $matches[1];
    } else {
        $lastSequence = 0;
    }

    $newSequence = str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);

    // Final project code
    $projectCode = "{$clientCode}/{$categoryCode}/{$year}/{$newSequence}";



    $data = $request->validated();
    $data['project_code'] = $projectCode;
    $data['requirement_id'] = $proposal->requirement_id;
    $data['proposal_id'] = $proposal->id;
    $data['status'] = Project::STATUS_INITIATED;

    $project = Project::create($data);
     $proposal->update(['project_status' =>1]);

    // Add team members
    // if ($request->has('team_members')) {
    //     foreach ($request->team_members as $memberId) {
    //         $project->teamMembers()->create([
    //             'user_id' => $memberId,
    //             'role' => 'member',
    //         ]);
    //     }
    // }

    // Add project investigator as lead
    // $project->teamMembers()->create([
    //     'user_id' => $request->project_investigator_id,
    //     'role' => 'lead',
    // ]);

    // Save project expense components (separate from proposal components)
        // foreach ($data['expense_components'] as $component) {
        //     $project->expenseComponents()->create([
        //         'expense_category_id' => $component['category_id'],
        //         'component' => $component['component'],
        //         'amount' => $component['amount'],
        //     ]);
        // }
        foreach ($data['expense_components'] as $component) {
    $project->expenseComponents()->create([
        'expense_category_id' => $component['category_id'],
        'group_name' => $component['group'] ?? 'Custom',
        'component' => $component['component'],
        'mandays' => $component['mandays'] ?? null,
        'rate' => $component['rate'] ?? null,
        'amount' => $component['amount'],
    ]);
}


    $project->teamMembers()->create([
            'user_id' => $request->project_investigator_id,
            'role' => 'lead',
            'expected_time_investment_minutes' => $request->pi_expected_time * 60,
        ]);
        if ($request->has('team_members_json')) {
          if ($request->filled('team_members_json')) {
        $teamMembers = json_decode($request->input('team_members_json'), true);
        foreach ($teamMembers as $member) {
            $project->teamMembers()->create([
                'user_id' => $member['user_id'],
                'role' => $member['role'],
                'expected_time_investment_minutes' => $member['expected_time'] * 60,
            ]);
        }
      }
      }

    // Copy documents from requirement and proposal to project
    // $this->copyDocumentsToProject($project, $proposal);

    return redirect()->route('pms.projects.show', $project->id)
        ->with('success', 'Project created successfully.');
}
    public function store(ProjectStoreRequest $request, Proposal $proposal)
    {
        if ($proposal->client_status != 'accepted' || !$proposal->workOrderDocuments()->exists()) {
            return redirect()->route('pms.proposals.show', $proposal->id)
                ->with('error', 'Project cannot be created for this proposal.');
        }

         $data = $request->validated();
        try {
            DB::beginTransaction();

            $requirement = $proposal->requirement;
            $clientCode = $requirement->client->code ?? 'CLNT';
            $categoryCode = $requirement->category->code ?? 'CAT';
            $year = $proposal->expected_start_date ? $proposal->expected_start_date->format('y') : now()->format('y');

            // Generate Project Code
            $lastProject = Project::whereHas('requirement.client', fn($q) => $q->where('code', $clientCode))
                ->whereHas('requirement.category', fn($q) => $q->where('code', $categoryCode))
                ->whereYear('start_date', '20' . $year)
                ->orderBy('id', 'desc')
                ->first();

            $lastSequence = ($lastProject && preg_match('/(\d+)$/', $lastProject->project_code, $matches)) ? (int)$matches[1] : 0;
            $newSequence = str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);
            $projectCode = "{$clientCode}/{$categoryCode}/{$year}/{$newSequence}";

            // 1. Create Project

            $data['project_code'] = $projectCode;
            $data['requirement_id'] = $proposal->requirement_id;
            $data['proposal_id'] = $proposal->id;
            $data['status'] = Project::STATUS_INITIATED;
            // Calculate total estimated expense from all years
            $totalEstimated = 0;
            if($request->has('yearly_estimates')) {
                foreach($request->yearly_estimates as $yearData) {
                    if(isset($yearData['components'])) {
                        foreach($yearData['components'] as $comp) {
                            $totalEstimated += $comp['amount'];
                        }
                    }
                }
            }
            $data['estimated_expense'] = $totalEstimated;

            $project = Project::create($data);
            $proposal->update(['project_status' => 1]);

            // 2. Save Yearly Budgets & Estimates
            if ($request->has('yearly_estimates')) {
                foreach ($request->yearly_estimates as $yearData) {
                    $financialYearId = $yearData['financial_year_id'];
                    $budgetAmount = $yearData['amount'];

                    // Create Yearly Budget Record
                    $yearlyBudget = $project->yearlyBudgets()->create([
                        'financial_year_id' => $financialYearId,
                        'amount' => $budgetAmount,
                        'notes' => $yearData['notes'] ?? null,
                    ]);

                    $yearTotalEstimated = 0;

                    // Save Estimated Components for this Year
                    if (isset($yearData['components'])) {
                        foreach ($yearData['components'] as $comp) {
                            $amount = $comp['amount'];
                            $yearTotalEstimated += $amount;

                            $project->expenseComponents()->create([
                                'expense_category_id' => $comp['category_id'] ?? ExpenseCategory::first()->id ?? 1,
                                'group_name' => $comp['group'],
                                'component' => $comp['component'],
                                'mandays' => $comp['mandays'] ?? null,
                                'rate' => $comp['rate'] ?? null,
                                'amount' => $amount,
                                'type' => ProjectExpenseComponent::TYPE_ESTIMATED,
                                'financial_year_id' => $financialYearId
                            ]);
                        }
                    }

                    // Update Yearly Budget with calculated estimate total
                    $yearlyBudget->update([
                        'yearly_estimated_expense' => $yearTotalEstimated,
                        'yearly_revenue' => $budgetAmount - $yearTotalEstimated
                    ]);
                }
            }

            // 3. Save Budgeted Components (Overall)
            if ($request->has('budgeted_components')) {
                foreach ($request->budgeted_components as $comp) {
                    $project->expenseComponents()->create([
                        'expense_category_id' => $comp['category_id'] ?? ExpenseCategory::first()->id ?? 1,
                        'group_name' => $comp['group'],
                        'component' => $comp['component'],
                        'mandays' => $comp['mandays'] ?? null,
                        'rate' => $comp['rate'] ?? null,
                        'amount' => $comp['amount'],
                        'type' => ProjectExpenseComponent::TYPE_BUDGETED,
                        'financial_year_id' => null
                    ]);
                }
            }

            // 4. Team Members
            $project->teamMembers()->create([
                'user_id' => $request->project_investigator_id,
                'role' => 'lead',
                'expected_time_investment_minutes' => $request->pi_expected_time * 60,
            ]);

            if ($request->has('team_members_json') && $request->filled('team_members_json')) {
                $teamMembers = json_decode($request->input('team_members_json'), true);
                if(is_array($teamMembers)) {
                    foreach ($teamMembers as $member) {
                        $project->teamMembers()->create([
                            'user_id' => $member['user_id'],
                            'role' => $member['role'],
                            'expected_time_investment_minutes' => ($member['expected_time'] ?? 0) * 60,
                        ]);
                    }
                }
            }

            // Handle Documents upload if any (omitted for brevity but can be added back)

            DB::commit();

            return redirect()->route('pms.projects.show', $project->id)
                ->with('success', 'Project created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating project: ' . $e->getMessage())->withInput();
        }
    }

    public function showold(Project $project)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $project->load([
            'requirement',
            'proposal',
            'investigator',
            'teamMembers.user',
            'milestones.tasks.assignments.user',
            'documents',
            'invoices.payments'
        ]);

        return view('pms.projects.show', compact('project'),['pageConfigs'=> $pageConfigs]);
    }
    public function show(Project $project)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $project->load([
            'requirement',
            'proposal',
            'investigator',
            'teamMembers.user',
            'milestones.tasks.assignments.user',
            'documents',
            'invoices.payments',
            'yearlyBudgets', // Added: Eager load yearly budgets
            'expenseComponents.category' // Added: Eager load expense components with category
        ]);

        return view('pms.projects.show', compact('project'),['pageConfigs'=> $pageConfigs]);
    }

    public function editOld(Project $project)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if ($project->status != Project::STATUS_INITIATED && $project->status != Project::STATUS_ONGOING && $project->status != Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Project cannot be edited in its current status.');
        }

        $project->load(['teamMembers']);
        $teamMemberIds = $project->teamMembers->pluck('user_id')->toArray();
        $teamMembersData = $project->teamMembers->map(function ($member) {
    return [
        'user_id' => $member->user_id,
        'name' => $member->user->name,
        'role' => $member->role,
        'expected_time' => $member->expected_time_investment_minutes
            ? $member->expected_time_investment_minutes / 60
            : 0
    ];
})->values()->toArray();

        $faculty = User::whereHas('roles', function($q) {
            $q->where('name', 'faculty');
        })->get();
        // $staff = User::whereHas('roles', function($q) {
        //     $q->where('name', 'staff');
        // })->get();
         $staff = User::where('active', 1)->get();
          // $expenseCategories = ExpenseCategory::whereNotIn('id',[2])->get();
           $expenseCategories = ExpenseCategory::all();

        return view('pms.projects.edit', compact('project', 'faculty', 'staff', 'teamMemberIds','teamMembersData','expenseCategories'),['pageConfigs'=> $pageConfigs]);
    }
    public function edit(Project $project)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if ($project->status != Project::STATUS_INITIATED && $project->status != Project::STATUS_ONGOING && $project->status != Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Project cannot be edited in its current status.');
        }

        $project->load(['teamMembers.user', 'yearlyBudgets', 'expenseComponents']);

        $teamMemberIds = $project->teamMembers->pluck('user_id')->toArray();
        $teamMembersData = $project->teamMembers->map(function ($member) {
            return [
                'user_id' => $member->user_id,
                'name' => $member->user->name,
                'role' => $member->role,
                'expected_time' => $member->expected_time_investment_minutes ? $member->expected_time_investment_minutes / 60 : 0
            ];
        })->values()->toArray();

        $faculty = User::whereHas('roles', function($q) { $q->where('name', 'faculty'); })->get();
        // $staff = User::where('active', 1)->get(); // Not used in view
        $expenseCategories = ExpenseCategory::all();

        // Financial Years (available for selection)
        // We need ALL financial years that cover the project duration, plus any that might be already attached even if dates changed
        $financialYears = FinancialYear::getForProjectPeriod(
            $project->start_date,
            $project->end_date
        );

        // Prepare Project Data JSON for JS
        $projectData = [
            'id' => $project->id,
            'title' => $project->title,
            'start_date' => $project->start_date->format('Y-m-d'),
            'end_date' => $project->end_date->format('Y-m-d'),
            'budget' => $project->budget,
            'estimated_expense' => $project->estimated_expense,
            'revenue' => $project->revenue,
            'description' => $project->description,
            'project_investigator_id' => $project->project_investigator_id,
            'pi_expected_time' => $project->teamMembers()->where('user_id', $project->project_investigator_id)->first()->expected_time_investment_minutes / 60 ?? 0,

            // Yearly Data
            'yearly_estimates' => [],

            // Budgeted (Overall) Components
            'budgeted_components' => $project->expenseComponents
                ->where('type', ProjectExpenseComponent::TYPE_BUDGETED)
                ->values()
                ->map(function($c) {
                    return [
                        'group' => $c->group_name,
                        'component' => $c->component,
                        'rate' => $c->rate,
                        'mandays' => $c->mandays,
                        'amount' => $c->amount,
                        'category_id' => $c->expense_category_id
                    ];
                })
        ];

        // Check if project has yearly budgets
        if ($project->yearlyBudgets->count() > 0) {
            // Project has yearly budgets - use existing data
            $projectData['yearly_estimates'] = $project->yearlyBudgets->map(function($yb) use ($project) {
                // Get components for this specific year
                $yearComponents = $project->expenseComponents
                    ->where('financial_year_id', $yb->financial_year_id)
                    ->where('type', ProjectExpenseComponent::TYPE_ESTIMATED)
                    ->values()
                    ->map(function($c) {
                        return [
                            'group' => $c->group_name,
                            'component' => $c->component,
                            'rate' => $c->rate,
                            'mandays' => $c->mandays,
                            'amount' => $c->amount,
                            'category_id' => $c->expense_category_id
                        ];
                    });

                return [
                    'financial_year_id' => $yb->financial_year_id,
                    'amount' => $yb->amount,
                    'notes' => $yb->notes,
                    'components' => $yearComponents
                ];
            })->values();
        } else {
            // No yearly budgets exist - auto-populate from estimated components without year assignment
            // Get current financial year or first available FY for project period
            $currentFY = FinancialYear::getCurrentFinancialYear();

            // If current FY doesn't overlap with project dates, use first FY from project period
            if (!$currentFY || !$financialYears->contains('id', $currentFY->id)) {
                $currentFY = $financialYears->first();
            }

            \Log::info('Auto-populate check', [
                'project_id' => $project->id,
                'current_fy' => $currentFY ? $currentFY->id : null,
                'total_estimated_components' => $project->expenseComponents->where('type', ProjectExpenseComponent::TYPE_ESTIMATED)->count(),
            ]);

            // Debug: Check all expense components
            $allEstimated = $project->expenseComponents->where('type', ProjectExpenseComponent::TYPE_ESTIMATED);
            // dd([
            //     'all_components_count' => $project->expenseComponents->count(),
            //     'all_estimated_count' => $allEstimated->count(),
            //     'all_estimated' => $allEstimated->map(function($c) {
            //         return [
            //             'id' => $c->id,
            //             'component' => $c->component,
            //             'type' => $c->type,
            //             'financial_year_id' => $c->financial_year_id,
            //         ];
            //     })->toArray()
            // ]);

            // Get estimated components that don't have a financial_year_id (legacy/unassigned)
            $unassignedEstimatedComponents = $project->expenseComponents
                ->where('type', ProjectExpenseComponent::TYPE_ESTIMATED)
                ->filter(function($c) {
                    // return is_null($c->financial_year_id);
                      return !$c->financial_year_id;
                });
                //  dd($unassignedEstimatedComponents);exit;

            \Log::info('Unassigned components', [
                'count' => $unassignedEstimatedComponents->count(),
                'components' => $unassignedEstimatedComponents->pluck('component', 'id')->toArray()
            ]);

            // Only auto-populate if we have a valid FY and unassigned estimated components exist
            if ($currentFY && $unassignedEstimatedComponents->count() > 0) {
                // Load unassigned estimated components into current year (UI display only)
                $estimatedComponents = $unassignedEstimatedComponents
                    ->values()
                    ->map(function($c) {
                        return [
                            'group' => $c->group_name,
                            'component' => $c->component,
                            'rate' => $c->rate,
                            'mandays' => $c->mandays,
                            'amount' => $c->amount,
                            'category_id' => $c->expense_category_id
                        ];
                    });

                $projectData['yearly_estimates'] = [[
                    'financial_year_id' => $currentFY->id,
                    'amount' => $project->budget ?? 0,
                    'notes' => 'Auto-populated from existing project data',
                    'components' => $estimatedComponents
                ]];

                \Log::info('Auto-populated data', [
                    'fy_id' => $currentFY->id,
                    'component_count' => $estimatedComponents->count()
                ]);
            } else {
                \Log::warning('Auto-populate skipped', [
                    'has_current_fy' => !is_null($currentFY),
                    'unassigned_count' => $unassignedEstimatedComponents->count()
                ]);
            }
        }

        return view('pms.projects.edit', compact(
            'project', 'faculty', 'teamMemberIds', 'teamMembersData',
            'expenseCategories', 'financialYears', 'projectData'
        ), ['pageConfigs'=> $pageConfigs]);
    }
    public function update(ProjectStoreRequest $request, Project $project)
    {
        if ($project->status != Project::STATUS_INITIATED && $project->status != Project::STATUS_ONGOING && $project->status != Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Project cannot be edited in its current status.');
        }

        try {
            DB::beginTransaction();

            // 1. Prepare Base Data & Totals
            $data = $request->validated();

            // Calculate total estimated expense from all years (re-verify backend side)
            $totalEstimated = 0;
            if($request->has('yearly_estimates')) {
                foreach($request->yearly_estimates as $yearData) {
                    if(isset($yearData['components'])) {
                        foreach($yearData['components'] as $comp) {
                            $totalEstimated += $comp['amount'];
                        }
                    }
                }
            }
            $data['estimated_expense'] = $totalEstimated;

            $project->update($data);

            // --- 2. Sync Yearly Budgets & Estimates ---
            // Strategy: Delete existing and recreate to ensure clean sync of complex nested structures.
            $project->yearlyBudgets()->delete();
            $project->expenseComponents()->delete(); // Delete ALL expense components (estimated AND budgeted)

            if ($request->has('yearly_estimates')) {
                foreach ($request->yearly_estimates as $yearData) {
                    $financialYearId = $yearData['financial_year_id'];
                    $budgetAmount = $yearData['amount'];

                    // Create Yearly Budget Record
                    $yearlyBudget = $project->yearlyBudgets()->create([
                        'financial_year_id' => $financialYearId,
                        'amount' => $budgetAmount,
                        'notes' => $yearData['notes'] ?? null,
                    ]);

                    $yearTotalEstimated = 0;

                    // Save Estimated Components for this Year
                    if (isset($yearData['components'])) {
                        foreach ($yearData['components'] as $comp) {
                            $amount = $comp['amount'];
                            $yearTotalEstimated += $amount;

                            $project->expenseComponents()->create([
                                'expense_category_id' => $comp['category_id'] ?? ExpenseCategory::first()->id ?? 1,
                                'group_name' => $comp['group'],
                                'component' => $comp['component'],
                                'mandays' => $comp['mandays'] ?? null,
                                'rate' => $comp['rate'] ?? null,
                                'amount' => $amount,
                                'type' => ProjectExpenseComponent::TYPE_ESTIMATED,
                                'financial_year_id' => $financialYearId
                            ]);
                        }
                    }

                    // Update Yearly Budget with calculated estimate total
                    $yearlyBudget->update([
                        'yearly_estimated_expense' => $yearTotalEstimated,
                        'yearly_revenue' => $budgetAmount - $yearTotalEstimated
                    ]);
                }
            }

            // --- 3. Sync Budgeted Components (Overall) ---
            // (Already deleted above with all components)

            if ($request->has('budgeted_components')) {
                foreach ($request->budgeted_components as $comp) {
                    $project->expenseComponents()->create([
                        'expense_category_id' => $comp['category_id'] ?? ExpenseCategory::first()->id ?? 1,
                        'group_name' => $comp['group'],
                        'component' => $comp['component'],
                        'mandays' => $comp['mandays'] ?? null,
                        'rate' => $comp['rate'] ?? null,
                        'amount' => $comp['amount'],
                        'type' => ProjectExpenseComponent::TYPE_BUDGETED,
                        'financial_year_id' => null
                    ]);
                }
            }

            // --- 4. Sync Team Members ---
            $project->teamMembers()->delete();

            // Re-add PI (Investigator)
            $project->teamMembers()->create([
                'user_id' => $request->project_investigator_id,
                'role' => 'lead',
                'expected_time_investment_minutes' => $request->pi_expected_time * 60,
            ]);

            // Add other members from JSON
            if ($request->has('team_members_json') && $request->filled('team_members_json')) {
                $teamMembers = json_decode($request->input('team_members_json'), true);
                if(is_array($teamMembers)) {
                    foreach ($teamMembers as $member) {
                        // Skip if same as PI (redundancy check)
                        if ($member['user_id'] == $request->project_investigator_id) continue;

                        $project->teamMembers()->create([
                            'user_id' => $member['user_id'],
                            'role' => $member['role'],
                            'expected_time_investment_minutes' => ($member['expected_time'] ?? 0) * 60,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('pms.projects.show', $project->id)
                ->with('success', 'Project updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating project: ' . $e->getMessage())->withInput();
        }
    }

    public function start(Project $project)
    {
        if ($project->status != Project::STATUS_INITIATED) {
            return redirect()->back()
                ->with('error', 'Project cannot be started in its current status.');
        }

        $project->update([
            'status' => Project::STATUS_ONGOING,
            'start_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Project started successfully.');
    }

    public function complete(Project $project)
    {
        if ($project->status != Project::STATUS_ONGOING) {
            return redirect()->back()
                ->with('error', 'Project cannot be completed in its current status.');
        }

        $project->update([
            'status' => Project::STATUS_COMPLETED,
            'end_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Project marked as completed successfully.');
    }

     public function archive(Project $project)
    {
        if ($project->status != Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Project cannot be archived in its current status.');
        }

        $project->update([
            'status' => Project::STATUS_ARCHIVED,
            'end_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Project marked as archived successfully.');
    }

    public function addDocument(Project $project, Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:10240',
            'type' => 'required|string|max:255',
        ]);

        $file = $request->file('document');
        $path = $file->store("public/projects/{$project->id}/documents");

        $project->documents()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $request->type,
            'size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Document uploaded successfully.');
    }

    // private function copyDocumentsToProject(Project $project, Proposal $proposal)
    // {
    //     // Copy requirement documents
    //     foreach ($proposal->requirement->documents as $document) {
    //         $project->documents()->create([
    //             'name' => $document->name,
    //             'path' => $document->path,
    //             'type' => $document->type,
    //             'size' => $document->size,
    //             'uploaded_by' => $document->uploaded_by,
    //         ]);
    //     }

    //     // Copy proposal documents
    //     foreach ($proposal->documents as $document) {
    //         $project->documents()->create([
    //             'name' => $document->name,
    //             'path' => $document->path,
    //             'type' => $document->type,
    //             'size' => $document->size,
    //             'uploaded_by' => $document->uploaded_by,
    //         ]);
    //     }
    // }
    private function copyDocumentsToProject(Project $project, Proposal $proposal)
{
    // Copy requirement documents
    foreach ($proposal->requirement->documents as $document) {
        $project->documents()->create([
            'name' => $document->name,
            'path' => $document->path,
            'type' => $document->type,
            'size' => $document->size,
            'uploaded_by' => $document->uploaded_by,
        ]);
    }

    // Copy proposal documents
    foreach ($proposal->documents as $document) {
        $project->documents()->create([
            'name' => $document->name,
            'path' => $document->path,
            'type' => $document->type,
            'size' => $document->size,
            'uploaded_by' => $document->uploaded_by,
        ]);
    }

    // Copy work order documents
    foreach ($proposal->workOrderDocuments as $document) {
        $project->documents()->create([
            'name' => $document->name,
            'path' => $document->path,
            'type' => 'Work Order',
            'size' => $document->size,
            'uploaded_by' => $document->uploaded_by,
        ]);
    }
}
    public function gantt(Project $project)
    {
        $project->load([
            'milestones.tasks.assignments.user',
            'teamMembers.user'
        ]);

        return view('pms.projects.gantt', compact('project'));
    }

        public function getTeamMembers(Project $project)
    {
        $members = $project->teamMembers()->with('user')->get();
        return response()->json($members);
    }



    //Dashboard
    public function dashboardOld($id)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $project = Project::with([
            'proposal.expenseComponents',
            'milestones.tasks',
            'invoices.payments',
            'expenses',
            'timesheets.user',
            'teamMembers.user'
        ])->findOrFail($id);

        // Budget vs Expenses
        $budget = $project->budget ? $project->budget : 0;
        $expenses = $project->expenses->sum('total_amount');
        $budgetRemaining = $budget - $expenses;
        $budgetUtilization = $budget > 0 ? ($expenses / $budget) * 100 : 0;

        // Revenue vs Invoices
        $totalInvoiced = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->sum('total_amount');
        $totalInvoiced_tax = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->where('invoice_type',2)->sum('total_amount');
        $totalInvoiced_proforma = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->where('invoice_type',1)->sum('total_amount');
        $totalPaid = $project->invoices->sum(function($invoice) {
            return $invoice->payments->sum('amount');
        });
        $outstanding = $totalInvoiced - $totalPaid;

        // Milestone progress
        $milestoneProgress = $project->milestones->map(function($milestone) {
            return [
                'name' => $milestone->name,
                'progress' => $milestone->getTaskCompletionPercentageAttribute(),
                'status' => $milestone->status_name
            ];
        });

        // Expense by category
        $expenseByCategory = $project->expenses->groupBy('category.name')->map(function($expenses, $category) {
            return $expenses->sum('total_amount');
        });

        // Timesheet data
        $timesheetByUser = $project->timesheets->groupBy('user.name')->map(function($timesheets) {
            return $timesheets->sum('minutes') / 60; // Convert to hours
        });

        // Recent activities
        $recentInvoices = $project->invoices()->latest()->take(5)->get();
        $recentExpenses = $project->expenses()->latest()->take(5)->get();
        $recentTasks = $project->tasks()->latest()->take(5)->get();

        return view('pms.projects.detailed_dashboard', compact(
            'project',
            'budget',
            'expenses',
            'budgetRemaining',
            'budgetUtilization',
            'totalInvoiced',
              'totalInvoiced_tax',
                'totalInvoiced_proforma',
            'totalPaid',
            'outstanding',
            'milestoneProgress',
            'expenseByCategory',
            'timesheetByUser',
            'recentInvoices',
            'recentExpenses',
            'recentTasks'
        ),['pageConfigs'=> $pageConfigs]);
    }
     public function dashboard($id)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $project = Project::with([
            'proposal.expenseComponents',
            'milestones.tasks',
            'invoices.payments',
            'expenses',
            'timesheets.user',
            'teamMembers.user',
            'yearlyBudgets.financialYear',
            'expenseComponents.category'
        ])->findOrFail($id);

        // Budget vs Expenses
        $budget = $project->budget ? $project->budget : 0;
        $expenses = $project->expenses->sum('total_amount');
        $budgetRemaining = $budget - $expenses;
        $budgetUtilization = $budget > 0 ? ($expenses / $budget) * 100 : 0;

        // Revenue vs Invoices
        $totalInvoiced = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->sum('total_amount');
        $totalInvoiced_tax = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->where('invoice_type',2)->sum('total_amount');
        $totalInvoiced_proforma = $project->invoices->whereIn('status', [Invoice::STATUS_SENT,Invoice::STATUS_PAID])->where('invoice_type',1)->sum('total_amount');
        $totalPaid = $project->invoices->sum(function($invoice) {
            return $invoice->payments->sum('amount');
        });
        $outstanding = $totalInvoiced - $totalPaid;

        // Milestone progress
        $milestoneProgress = $project->milestones->map(function($milestone) {
            return [
                'name' => $milestone->name,
                'progress' => $milestone->getTaskCompletionPercentageAttribute(),
                'status' => $milestone->status_name
            ];
        });

        // Expense by category
        $expenseByCategory = $project->expenses->groupBy('category.name')->map(function($expenses, $category) {
            return $expenses->sum('total_amount');
        });

        // Timesheet data
        $timesheetByUser = $project->timesheets->groupBy('user.name')->map(function($timesheets) {
            return $timesheets->sum('minutes') / 60; // Convert to hours
        });

        // Recent activities
        $recentInvoices = $project->invoices()->latest()->take(5)->get();
        $recentExpenses = $project->expenses()->latest()->take(5)->get();
        $recentTasks = $project->tasks()->latest()->take(5)->get();

        return view('pms.projects.detailed_dashboard', compact(
            'project',
            'budget',
            'expenses',
            'budgetRemaining',
            'budgetUtilization',
            'totalInvoiced',
              'totalInvoiced_tax',
                'totalInvoiced_proforma',
            'totalPaid',
            'outstanding',
            'milestoneProgress',
            'expenseByCategory',
            'timesheetByUser',
            'recentInvoices',
            'recentExpenses',
            'recentTasks'
        ),['pageConfigs'=> $pageConfigs]);
    }

    public function getProjectFinancialData($id)
    {
        $project = Project::findOrFail($id);

        $financialData = [
            'budget' => $project->proposal ? $project->proposal->budget : 0,
            'expenses' => $project->expenses->sum('total_amount'),
            'invoiced' => $project->invoices->sum('total_amount'),
            'paid' => $project->invoices->sum(function($invoice) {
                return $invoice->payments->sum('amount');
            })
        ];

        return response()->json($financialData);
    }

    public function getProjectTimelineData($id)
    {
        $project = Project::with(['milestones.tasks'])->findOrFail($id);

        $timelineData = [
            'milestones' => $project->milestones->map(function($milestone) {
                return [
                    'name' => $milestone->name,
                    'progress' => $milestone->getTaskCompletionPercentageAttribute(),
                    'status' => $milestone->status_name,
                    'tasks' => $milestone->tasks->map(function($task) {
                        return [
                            'name' => $task->name,
                            'status' => $task->status_name
                        ];
                    })
                ];
            })
        ];

        return response()->json($timelineData);
    }
}
