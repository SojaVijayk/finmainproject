<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\ProposalStoreRequest;
use App\Http\Requests\PMS\ProposalUpdateRequest;
use App\Models\PMS\Proposal;
use App\Models\PMS\ProposalStatusLog;
use App\Models\PMS\Requirement;
use App\Models\PMS\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $proposals = Proposal::with(['requirement', 'creator'])
         ->where('created_by', Auth::id())
         ->where('project_status',0)

            ->latest()
            ->paginate(20);

        return view('pms.proposals.index', compact('proposals'),['pageConfigs'=> $pageConfigs]);
    }

    public function create(Requirement $requirement)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
      $expenseCategories = ExpenseCategory::all();
      if(!in_array($requirement->status, [Requirement::STATUS_APPROVED_BY_DIRECTOR,Requirement::STATUS_APPROVED_BY_PAC]) || $requirement->allocated_to != Auth::id()){
        // if ($requirement->status != Requirement::STATUS_APPROVED_BY_DIRECTOR || $requirement->allocated_to != Auth::id()) {
            return redirect()->route('pms.requirements.show', $requirement->id)
                ->with('error', 'You cannot create a proposal for this requirement.');
        }

        return view('pms.proposals.create', compact('requirement','expenseCategories'),['pageConfigs'=> $pageConfigs]);
    }

    public function storeOld(ProposalStoreRequest $request, Requirement $requirement)
    {

        if(!in_array($requirement->status, [Requirement::STATUS_APPROVED_BY_DIRECTOR,Requirement::STATUS_APPROVED_BY_PAC]) || $requirement->allocated_to != Auth::id()){
            return redirect()->route('pms.requirements.show', $requirement->id)
                ->with('error', 'You cannot create a proposal for this requirement.');
        }



        $data = $request->validated();

        $data['requirement_id'] = $requirement->id;
        $data['created_by'] = Auth::id();
        $data['status'] = Proposal::STATUS_CREATED;
        // DB::transaction(function () use ($data, $requirement) {
        $proposal = Proposal::create($data);



        foreach ($data['expense_components'] as $component) {
    $proposal->expenseComponents()->create([
        'expense_category_id' => $component['category_id'],
        'group_name' => $component['group'] ?? 'Custom',
        'component' => $component['component'],
        'mandays' => $component['mandays'] ?? null,
        'rate' => $component['rate'] ?? null,
        'amount' => $component['amount'],
    ]);
}


        // Handle document uploads if any
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('public/proposals/documents');

                $proposal->documents()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }
        $requirement->update(['proposal_status' =>1]);

        //  });

        return redirect()->route('pms.proposals.show', $proposal->id)
            ->with('success', 'Proposal created successfully.');
    }

  public function store(ProposalStoreRequest $request, Requirement $requirement)
{
    if(!in_array($requirement->status, [Requirement::STATUS_APPROVED_BY_DIRECTOR,Requirement::STATUS_APPROVED_BY_PAC]) || $requirement->allocated_to != Auth::id()){
        return redirect()->route('pms.requirements.show', $requirement->id)
            ->with('error', 'You cannot create a proposal for this requirement.');
    }

    $data = $request->validated();
    $data['requirement_id'] = $requirement->id;
    $data['created_by'] = Auth::id();
    $data['status'] = Proposal::STATUS_CREATED;

    // Calculate estimated expense from components
    $totalEstimated = 0;
    if (isset($data['expense_components'])) {
        foreach ($data['expense_components'] as $component) {
            $totalEstimated += $component['amount'] ?? 0;
        }
    }
    $data['estimated_expense'] = $totalEstimated;

    // Calculate revenue
    $data['revenue'] = ($data['budget'] ?? 0) - $totalEstimated;

    $proposal = Proposal::create($data);

    // Save estimated expense components (type = 1)
    if (isset($data['expense_components'])) {
        foreach ($data['expense_components'] as $key => $component) {
            // Handle HR components with mandays calculation
            $amount = $component['amount'] ?? 0;
            if (isset($component['mandays']) && isset($component['rate'])) {
                $mandays = $component['mandays'] ?? 0;
                $rate = $component['rate'] ?? 0;
                $amount = $mandays * $rate;
            }

            // Get the first expense category if not provided or invalid
            $categoryId = $component['category_id'] ?? null;
            if (!$categoryId || !ExpenseCategory::find($categoryId)) {
                $firstCategory = ExpenseCategory::first();
                $categoryId = $firstCategory ? $firstCategory->id : null;
            }

            $proposal->expenseComponents()->create([
                'expense_category_id' => $categoryId,
                'group_name' => $component['group'] ?? 'Custom',
                'component' => $component['component'] ?? 'Custom Component',
                'mandays' => $component['mandays'] ?? null,
                'rate' => $component['rate'] ?? null,
                'amount' => $amount,
                'type' => Proposal::TYPE_ESTIMATED, // Estimated expense
            ]);
        }
    }

    // Save budgeted expense components if copy button was clicked (type = 0)
    $hasBudgetComponents = false;
    if ($request->has('copy_to_budget') && $request->copy_to_budget == '1') {
        $hasBudgetComponents = true;

        // Save cloned components
        // if (isset($data['expense_components'])) {
        //     foreach ($data['expense_components'] as $key => $component) {
        //         // Get budget values if provided, otherwise use estimated values
        //         $budgetAmount = $request->budgeted_expense_components[$key]['amount'] ?? $component['amount'] ?? 0;
        //         $budgetMandays = $request->budgeted_expense_components[$key]['mandays'] ?? $component['mandays'] ?? null;
        //         $budgetRate = $component['rate'] ?? null;

        //         // Recalculate amount for HR components if mandays changed
        //         if ($budgetMandays && $budgetRate) {
        //             $budgetAmount = $budgetMandays * $budgetRate;
        //         }

        //         // Get the first expense category if not provided or invalid
        //         $categoryId = $component['category_id'] ?? null;
        //         if (!$categoryId || !ExpenseCategory::find($categoryId)) {
        //             $firstCategory = ExpenseCategory::first();
        //             $categoryId = $firstCategory ? $firstCategory->id : null;
        //         }

        //         $proposal->expenseComponents()->create([
        //             'expense_category_id' => $categoryId,
        //             'group_name' => $component['group'] ?? 'Custom',
        //             'component' => $component['component'] ?? 'Custom Component',
        //             'mandays' => $budgetMandays,
        //             'rate' => $budgetRate,
        //             'amount' => $budgetAmount,
        //             'type' => Proposal::TYPE_BUDGETED, // Budgeted expense
        //         ]);
        //     }
        // }

        // Save custom budget components (those starting with custom_budget_)
        if ($request->has('budgeted_expense_components')) {
            foreach ($request->budgeted_expense_components as $key => $component) {
                // if (str_starts_with($key, 'custom_budget_')) {
                    // Get the first expense category if not provided or invalid
                    $categoryId = $component['category_id'] ?? null;
                    if (!$categoryId || !ExpenseCategory::find($categoryId)) {
                        $firstCategory = ExpenseCategory::first();
                        $categoryId = $firstCategory ? $firstCategory->id : null;
                    }

                    $proposal->expenseComponents()->create([
                        'expense_category_id' => $categoryId,
                        'group_name' => $component['group'] ?? 'Custom',
                        'component' => $component['component'] ?? 'Custom Budget Component',
                        'mandays' => $component['mandays'] ?? null,
                        'rate' => $component['rate'] ?? null,
                        'amount' => $component['amount'] ?? 0,
                        'type' => Proposal::TYPE_BUDGETED, // Budgeted expense
                    ]);
                // }
            }
        }
    }

    // Handle document uploads if any
    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            $path = $file->store('public/proposals/documents');

            $proposal->documents()->create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    // Update requirement proposal status
    $requirement->update(['proposal_status' => 1]);

    // // Log the creation
    // activity()
    //     ->performedOn($proposal)
    //     ->causedBy(Auth::user())
    //     ->withProperties([
    //         'has_budget_components' => $hasBudgetComponents,
    //         'estimated_expense' => $data['estimated_expense'],
    //         'budgeted_expense' => $hasBudgetComponents ? $proposal->total_budgeted_expense : 0,
    //     ])
    //     ->log('Proposal created with ' . ($hasBudgetComponents ? 'estimated and budgeted expenses' : 'estimated expenses only'));

    return redirect()->route('pms.proposals.show', $proposal->id)
        ->with('success', 'Proposal created successfully.');
}

    public function show(Proposal $proposal)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $proposal->load([
            'requirement',
            'creator',
            'documents',
            'expenseComponents',
            'workOrderDocuments',
            'statusLogs.changedBy',
            'clientStatusLogs.changedBy'
        ]);

        return view('pms.proposals.show', compact('proposal'),['pageConfigs'=> $pageConfigs]);
    }

    public function edit(Proposal $proposal)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if ($proposal->status != Proposal::STATUS_CREATED && $proposal->status != Proposal::STATUS_RETURNED_FOR_CLARIFICATION) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be edited in its current status.');
        }

        if ($proposal->created_by != Auth::id()) {
            return redirect()->back()
                ->with('error', 'You are not authorized to edit this proposal.');
        }
           $expenseCategories = ExpenseCategory::whereNotIn('id',[2])->get();

        return view('pms.proposals.edit', compact('proposal','expenseCategories'),['pageConfigs'=> $pageConfigs]);
    }
public function update(ProposalUpdateRequest $request, Proposal $proposal)
{
    if ($proposal->status != Proposal::STATUS_CREATED && $proposal->status != Proposal::STATUS_RETURNED_FOR_CLARIFICATION) {
        return redirect()->back()
            ->with('error', 'Proposal cannot be edited in its current status.');
    }

    if ($proposal->created_by != Auth::id()) {
        return redirect()->back()
            ->with('error', 'You are not authorized to edit this proposal.');
    }

    $data = $request->validated();

    // Calculate estimated expense from components if not provided
    if (!isset($data['estimated_expense']) && isset($data['expense_components'])) {
        $totalEstimated = 0;
        foreach ($data['expense_components'] as $component) {
            $totalEstimated += $component['amount'] ?? 0;
        }
        $data['estimated_expense'] = $totalEstimated;
    }

    // Calculate revenue
    $data['revenue'] = ($data['budget'] ?? 0) - ($data['estimated_expense'] ?? 0);

    $proposal->update($data);

    // Delete existing expense components
    $proposal->expenseComponents()->delete();

    // Save estimated expense components (type = 1)
    if (isset($data['expense_components'])) {
        foreach ($data['expense_components'] as $key => $component) {
            // Handle HR components with mandays calculation
            $amount = $component['amount'] ?? 0;
            if (isset($component['mandays']) && isset($component['rate'])) {
                $mandays = $component['mandays'] ?? 0;
                $rate = $component['rate'] ?? 0;
                $amount = $mandays * $rate;
            }

            // Ensure category_id exists or use default
            $categoryId = $component['category_id'] ?? null;
            if (!$categoryId || !ExpenseCategory::find($categoryId)) {
                $firstCategory = ExpenseCategory::first();
                $categoryId = $firstCategory ? $firstCategory->id : null;
            }

            $proposal->expenseComponents()->create([
                'expense_category_id' => $categoryId,
                'group_name' => $component['group'] ?? 'Custom',
                'component' => $component['component'] ?? 'Custom Component',
                'mandays' => $component['mandays'] ?? null,
                'rate' => $component['rate'] ?? null,
                'amount' => $amount,
                'type' => Proposal::TYPE_ESTIMATED, // Estimated expense
            ]);
        }
    }

    // Save budgeted expense components if they exist
    $hasBudgetComponents = false;
    if ($request->has('copy_to_budget') && $request->copy_to_budget == '1') {
        $hasBudgetComponents = true;

        // Save cloned components
        // if (isset($data['expense_components'])) {
        //     foreach ($data['expense_components'] as $key => $component) {
        //         // Get budget values if provided, otherwise use estimated values
        //         $budgetAmount = $request->budgeted_expense_components[$key]['amount'] ?? $component['amount'] ?? 0;
        //         $budgetMandays = $request->budgeted_expense_components[$key]['mandays'] ?? $component['mandays'] ?? null;
        //         $budgetRate = $component['rate'] ?? null;

        //         // Recalculate amount for HR components if mandays changed
        //         if ($budgetMandays && $budgetRate) {
        //             $budgetAmount = $budgetMandays * $budgetRate;
        //         }

        //         // Ensure category_id exists or use default
        //         $categoryId = $component['category_id'] ?? null;
        //         if (!$categoryId || !ExpenseCategory::find($categoryId)) {
        //             $firstCategory = ExpenseCategory::first();
        //             $categoryId = $firstCategory ? $firstCategory->id : null;
        //         }

        //         $proposal->expenseComponents()->create([
        //             'expense_category_id' => $categoryId,
        //             'group_name' => $component['group'] ?? 'Custom',
        //             'component' => $component['component'] ?? 'Custom Component',
        //             'mandays' => $budgetMandays,
        //             'rate' => $budgetRate,
        //             'amount' => $budgetAmount,
        //             'type' => Proposal::TYPE_BUDGETED, // Budgeted expense
        //         ]);
        //     }
        // }

        // Save custom budget components (those starting with custom_budget_)
        if ($request->has('budgeted_expense_components')) {
            foreach ($request->budgeted_expense_components as $key => $component) {
                // if (str_starts_with($key, 'custom_budget_')) {
                    // Ensure category_id exists or use default
                    $categoryId = $component['category_id'] ?? null;
                    if (!$categoryId || !ExpenseCategory::find($categoryId)) {
                        $firstCategory = ExpenseCategory::first();
                        $categoryId = $firstCategory ? $firstCategory->id : null;
                    }

                    $proposal->expenseComponents()->create([
                        'expense_category_id' => $categoryId,
                        'group_name' => $component['group'] ?? 'Custom',
                        'component' => $component['component'] ?? 'Custom Budget Component',
                        'mandays' => $component['mandays'] ?? null,
                        'rate' => $component['rate'] ?? null,
                        'amount' => $component['amount'] ?? 0,
                        'type' => Proposal::TYPE_BUDGETED, // Budgeted expense
                    ]);
                // }
            }
        }
    }

    // Handle document uploads if any
    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            $path = $file->store('public/proposals/documents');

            $proposal->documents()->create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    // // Log the update
    // activity()
    //     ->performedOn($proposal)
    //     ->causedBy(Auth::user())
    //     ->withProperties([
    //         'has_budget_components' => $hasBudgetComponents,
    //         'estimated_expense' => $data['estimated_expense'] ?? $proposal->estimated_expense,
    //         'budgeted_expense' => $hasBudgetComponents ? $proposal->total_budgeted_expense : 0,
    //     ])
    //     ->log('Proposal updated with ' . ($hasBudgetComponents ? 'estimated and budgeted expenses' : 'estimated expenses only'));

    return redirect()->route('pms.proposals.show', $proposal->id)
        ->with('success', 'Proposal updated successfully.');
}

    public function updateOld(ProposalUpdateRequest $request, Proposal $proposal)
    {
        if ($proposal->status != Proposal::STATUS_CREATED && $proposal->status != Proposal::STATUS_RETURNED_FOR_CLARIFICATION) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be edited in its current status.');
        }

        if ($proposal->created_by != Auth::id()) {
            return redirect()->back()
                ->with('error', 'You are not authorized to edit this proposal.');
        }

        $data = $request->validated();
        $proposal->update($data);


          // Delete existing expense components and create new ones
        $proposal->expenseComponents()->delete();

        // foreach ($data['expense_components'] as $component) {
        //     $proposal->expenseComponents()->create([
        //         'expense_category_id' => $component['category_id'],
        //         'component' => $component['component'],
        //         'amount' => $component['amount'],
        //     ]);
        // }
         foreach ($data['expense_components'] as $component) {
            $proposal->expenseComponents()->create([
                'expense_category_id' => $component['category_id'] ?? null,
                'group_name' => $component['group'] ?? 'Custom',
                'component' => $component['component'] ?? 'Unnamed',
                'mandays' => $component['mandays'] ?? null,
                'rate' => $component['rate'] ?? null,
                'amount' => $component['amount'] ?? 0,
            ]);
        }



        // Handle document uploads if any
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('public/proposals/documents');

                $proposal->documents()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('pms.proposals.show', $proposal->id)
            ->with('success', 'Proposal updated successfully.');
    }

    public function submitForApproval(Proposal $proposal)
    {
        if ($proposal->status != Proposal::STATUS_CREATED && $proposal->status != Proposal::STATUS_RETURNED_FOR_CLARIFICATION) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be submitted for approval in its current status.');
        }

        if ($proposal->created_by != Auth::id()) {
            return redirect()->back()
                ->with('error', 'You are not authorized to submit this proposal.');
        }

        $proposal->update([
            'status' => Proposal::STATUS_SENT_TO_DIRECTOR,
        ]);

        // Log status change
        ProposalStatusLog::create([
            'proposal_id' => $proposal->id,
            'from_status' => $proposal->status,
            'to_status' => Proposal::STATUS_SENT_TO_DIRECTOR,
            'changed_by' => Auth::id(),
            'comments' => 'Submitted for director approval',
        ]);

        // TODO: Notify director

        return redirect()->back()
            ->with('success', 'Proposal submitted for approval successfully.');
    }

    public function approve(Proposal $proposal)
    {
        if ($proposal->status != Proposal::STATUS_SENT_TO_DIRECTOR) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be approved in its current status.');
        }

        $proposal->update([
            'status' => Proposal::STATUS_APPROVED_BY_DIRECTOR,
        ]);

        // Log status change
        ProposalStatusLog::create([
            'proposal_id' => $proposal->id,
            'from_status' => Proposal::STATUS_SENT_TO_DIRECTOR,
            'to_status' => Proposal::STATUS_APPROVED_BY_DIRECTOR,
            'changed_by' => Auth::id(),
            'comments' => 'Approved by director',
        ]);

        // TODO: Notify creator

        return redirect()->back()
            ->with('success', 'Proposal approved successfully.');
    }

    public function reject(Proposal $proposal)
    {
        if ($proposal->status != Proposal::STATUS_SENT_TO_DIRECTOR) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be rejected in its current status.');
        }

        $proposal->update([
            'status' => Proposal::STATUS_REJECTED,
        ]);

        // Log status change
        ProposalStatusLog::create([
            'proposal_id' => $proposal->id,
            'from_status' => Proposal::STATUS_SENT_TO_DIRECTOR,
            'to_status' => Proposal::STATUS_REJECTED,
            'changed_by' => Auth::id(),
            'comments' => 'Rejected by director',
        ]);

        // TODO: Notify creator

        return redirect()->back()
            ->with('success', 'Proposal rejected successfully.');
    }

    public function returnForClarification(Proposal $proposal, Request $request)
    {
        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        if ($proposal->status != Proposal::STATUS_SENT_TO_DIRECTOR) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be returned for clarification in its current status.');
        }

        $proposal->update([
            'status' => Proposal::STATUS_RETURNED_FOR_CLARIFICATION,
        ]);

        // Log status change
        ProposalStatusLog::create([
            'proposal_id' => $proposal->id,
            'from_status' => Proposal::STATUS_SENT_TO_DIRECTOR,
            'to_status' => Proposal::STATUS_RETURNED_FOR_CLARIFICATION,
            'changed_by' => Auth::id(),
            'comments' => $request->comments,
        ]);

        // TODO: Notify creator

        return redirect()->back()
            ->with('success', 'Proposal returned for clarification successfully.');
    }

    public function sendToClient(Proposal $proposal)
    {
        if ($proposal->status != Proposal::STATUS_APPROVED_BY_DIRECTOR) {
            return redirect()->back()
                ->with('error', 'Proposal cannot be sent to client in its current status.');
        }

        if ($proposal->requirement->allocated_to != Auth::id()) {
            return redirect()->back()
                ->with('error', 'You are not authorized to send this proposal to client.');
        }

        // TODO: Implement email sending to client
        // TODO: Record the sending in a log

        return redirect()->back()
            ->with('success', 'Proposal sent to client successfully.');
    }

    // public function updateClientStatus(Proposal $proposal, Request $request)
    // {
    //     $request->validate([
    //         'client_status' => 'required|in:accepted,rejected,resubmit_requested',
    //         'comments' => 'nullable|string|max:1000',
    //         'documents' => 'nullable|array',
    //         'documents.*' => 'file|max:10240',
    //     ]);

    //     if ($proposal->requirement->allocated_to != Auth::id()) {
    //         return redirect()->back()
    //             ->with('error', 'You are not authorized to update client status for this proposal.');
    //     }

    //     // TODO: Update client status and record the change
    //     // TODO: If accepted, handle work order documents upload

    //     return redirect()->back()
    //         ->with('success', 'Client status updated successfully.');
    // }

    public function updateClientStatus(Proposal $proposal, Request $request)
  {

    $request->validate([
        'client_status' => 'required|in:accepted,rejected,resubmit_requested',
        'client_comments' => 'nullable|string|max:1000',
        'documents' => 'nullable|array',
        'documents.*' => 'file|max:10240',
    ]);

// dd($request->all());exit;

    if ($proposal->requirement->allocated_to != Auth::id()) {
        return redirect()->back()
            ->with('error', 'You are not authorized to update client status for this proposal.');
    }

       // Update client status
         $oldStatus = $proposal->client_status;
    if($request->client_status == 'resubmit_requested'){


         $proposal->update([
        'client_status' => $request->client_status,
        'client_comments' => $request->client_comments,
        'client_status_updated_at' => now(),
        'client_status_updated_by' => Auth::id(),
        'status' => Proposal::STATUS_RETURNED_FOR_CLARIFICATION,
    ]);

        // Log status change
        ProposalStatusLog::create([
            'proposal_id' => $proposal->id,
            'from_status' => Proposal::STATUS_SENT_TO_DIRECTOR,
            'to_status' => Proposal::STATUS_RETURNED_FOR_CLARIFICATION,
            'changed_by' => Auth::id(),
            'comments' => $request->client_comments,
        ]);
    }
    if($request->client_status == 'accepted'){
        $proposal->update([
        'client_status' => $request->client_status,
        'client_comments' => $request->client_comments,
        'client_status_updated_at' => now(),
        'client_status_updated_by' => Auth::id()
    ]);
    }

     // Store in log table
    $proposal->clientStatusLogs()->create([
        'from_status' => $oldStatus,
        'to_status' => $request->client_status,
        'comments' => $request->client_comments,
        'changed_by' => Auth::id(),
    ]);



    // Handle document uploads if any
    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            $path = $file->store('public/proposals/work-orders');

            $proposal->workOrderDocuments()->create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'category' =>'Work order',
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

       if ($request->filled('selected_files')) {
        foreach ($request->selected_files as $filePath) {
            // You can either "link" the file or "copy" it to requirements folder.
            // Option 1 (recommended): Just link it
            $proposal->workOrderDocuments()->create([
                'name' => basename($filePath),
                'path' => $filePath,
                'type' => mime_content_type(storage_path('app/' . $filePath)),
                'size' => filesize(storage_path('app/' . $filePath)),
                'uploaded_by' => Auth::id(),
                'category'=>'Work order',
                'source' => 'linked', // mark as linked instead of uploaded
            ]);

            // Option 2 (if you prefer to make a copy):

            // $newPath = 'public/requirements/documents/' . basename($filePath);
            // Storage::copy($filePath, $newPath);
            // $requirement->documents()->create([
            //     'name' => basename($filePath),
            //     'path' => $newPath,
            //     'type' => mime_content_type(storage_path('app/' . $filePath)),
            //     'size' => filesize(storage_path('app/' . $filePath)),
            //     'uploaded_by' => Auth::id(),
            //     'source' => 'copied',
            // ]);

        }
    }

    // Log the status change
    ProposalStatusLog::create([
        'proposal_id' => $proposal->id,
        'from_status' => $proposal->status,
        'to_status' => $proposal->status, // Status remains same, only client status changes
        'changed_by' => Auth::id(),
        'comments' => 'Client status updated to: ' . $request->client_status .
                     ($request->comments ? '. Comments: ' . $request->comments : '')
    ]);

    return redirect()->back()
        ->with('success', 'Client status updated successfully.');
  }
}