<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\MilestoneStoreRequest;
use App\Http\Requests\PMS\MilestoneUpdateRequest;
use App\Models\PMS\Milestone;
use App\Models\PMS\Task;;
use App\Models\PMS\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    public function index(Project $project)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $milestones = $project->milestones()
            ->with(['tasks.assignments.user'])
            ->orderBy('start_date')
            ->get();

        return view('pms.milestones.index', compact('project', 'milestones'),['pageConfigs'=> $pageConfigs]);
    }

    public function create(Project $project)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if ($project->status == Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Cannot add milestones to a completed project.');
        }

        return view('pms.milestones.create', compact('project'),['pageConfigs'=> $pageConfigs]);
    }

    public function store(MilestoneStoreRequest $request, Project $project)
    {
      // dd($request->all(), $project->id);exit;

        if ($project->status == Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Cannot add milestones to a completed project.');
        }

        $data = $request->validated();

        $milestone = $project->milestones()->create($data);


        return redirect()->route('pms.milestones.show', [$project->id, $milestone->id])
            ->with('success', 'Milestone created successfully.');
    }

    public function show(Project $project, Milestone $milestone)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $milestone->load(['tasks.assignments.user', 'project']);

        return view('pms.milestones.show', compact('project', 'milestone'),['pageConfigs'=> $pageConfigs]);
    }

    public function edit(Project $project, Milestone $milestone)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if ($milestone->status == Milestone::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Completed milestone cannot be edited.');
        }

        return view('pms.milestones.edit', compact('project', 'milestone'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(MilestoneUpdateRequest $request, Project $project, Milestone $milestone)
    {
        if ($milestone->status == Milestone::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Completed milestone cannot be edited.');
        }

        $data = $request->validated();
        $milestone->update($data);

        return redirect()->route('pms.milestones.show', [$project->id, $milestone->id])
            ->with('success', 'Milestone updated successfully.');
    }

    public function start(Project $project, Milestone $milestone)
    {
        if ($milestone->status != Milestone::STATUS_NOT_STARTED) {
            return redirect()->back()
                ->with('error', 'Milestone cannot be started in its current status.');
        }

        $milestone->update([
            'status' => Milestone::STATUS_IN_PROGRESS,
            'start_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Milestone started successfully.');
    }

    public function complete(Project $project, Milestone $milestone)
    {
        if ($milestone->status != Milestone::STATUS_IN_PROGRESS) {
            return redirect()->back()
                ->with('error', 'Milestone cannot be completed in its current status.');
        }

        // Check if all tasks are completed
        $incompleteTasks = $milestone->tasks()->where('status', '!=', Task::STATUS_COMPLETED)->count();
        if ($incompleteTasks > 0) {
            return redirect()->back()
                ->with('error', 'All tasks must be completed before completing the milestone.');
        }

        $milestone->update([
            'status' => Milestone::STATUS_COMPLETED,
            'end_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Milestone marked as completed successfully.');
    }

    public function requestInvoice(Project $project, Milestone $milestone)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if (!$milestone->invoice_trigger) {
            return redirect()->back()
                ->with('error', 'This milestone is not marked as an invoice trigger.');
        }

        if ($milestone->status != Milestone::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Only completed milestones can trigger invoice requests.');
        }

        // Check if invoice already exists for this milestone
        if ($milestone->invoice) {
            return redirect()->back()
                ->with('error', 'An invoice already exists for this milestone.');
        }

        // TODO: Create invoice request
        // This would typically notify the finance team

        if ($project->status == Project::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Cannot create invoices for a completed project.');
        }

        $milestones = $project->milestones()
            ->where('invoice_trigger', true)
            ->whereDoesntHave('invoice')
            ->get();

        return view('pms.invoices.create', compact('project', 'milestones'),['pageConfigs'=> $pageConfigs]);


        // return redirect()->back()
        //     ->with('success', 'Invoice request submitted successfully. Finance team will process it.');
    }
}