<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Project;
use App\Models\PMS\Task;
use App\Models\PMS\Timesheet;
use App\Models\PMS\Requirement;
use App\Models\PMS\Proposal;
use App\Models\PMS\Invoice;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $user = Auth::user();
        $data = [];

        // PROJECT LISTING BASED ON ROLE
        if ($user->hasRole('director')) {
            // Director sees ALL projects
            $projects = Project::with(['investigator', 'teamMembers'])->get();
            $proposalSubmitted_query = Proposal::where('project_status', 0)->get();
             $planningStage_query = Requirement::where('proposal_status', 0)->get();
        } else {
            // Investigator or Team Member
            $projects = Project::with(['investigator', 'teamMembers'])
                ->where(function ($q) use ($user) {
                    $q->where('project_investigator_id', $user->id)
                      ->orWhereHas('teamMembers', function ($tq) use ($user) {
                          $tq->where('user_id', $user->id);
                      });
                })
                ->get();

                 $proposalSubmitted_query = Proposal::where('project_status', 0)->where('created_by', $user->id)->get();

                 $planningStage_query = Requirement::where('proposal_status', 0)->where('created_by', $user->id)->get();

        }

        // Common statistics for all users
        $data['total_projects'] = $projects->count();
        $data['active_projects'] = $projects->where('status', Project::STATUS_ONGOING)->count();
        $data['completed_projects'] = $projects->where('status', Project::STATUS_COMPLETED)->count();
          $data['delayed_projects'] = $projects->where('status', Project::STATUS_ONGOING)->where('end_date', '<', Carbon::today())->count();
         $data['proposal_submitted_projects'] = $proposalSubmitted_query->count();
         $data['planning_projects'] = $planningStage_query->count();
         $data['archived'] = $projects->where('status', Project::STATUS_ARCHIVED)->count();

        // Role-specific data
        // if ($user->hasRole('team_member')) {
            $data['assigned_tasks'] = Task::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', '!=', Task::STATUS_COMPLETED)->count();

            $data['today_timesheet'] = Timesheet::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->sum('minutes');
        // }

        // if ($user->hasRole('team_lead')) {
            $data['team_projects'] = Project::whereHas('teamMembers', function($q) use ($user) {
                // $q->where('user_id', $user->id)->where('role', 'lead');
                $q->where('user_id', $user->id);
            })->count();

            $data['pending_tasks'] = Task::whereHas('milestone.project.teamMembers', function($q) use ($user) {
                $q->where('user_id', $user->id)->where('role', 'lead');
            })->where('status', Task::STATUS_NOT_STARTED)->count();
        // }

        if ($user->hasRole('director')) {
            $data['pending_approvals'] = Requirement::whereIn('status', [
                Requirement::STATUS_SENT_TO_DIRECTOR,
                Requirement::STATUS_SENT_TO_PAC
            ])->count() + Proposal::where('status', Proposal::STATUS_SENT_TO_DIRECTOR)->count();
        }

        if ($user->hasRole('finance')) {
            $data['pending_invoices'] = Invoice::where('status', Invoice::STATUS_SENT)->count();
            $data['overdue_invoices'] = Invoice::where('status', Invoice::STATUS_OVERDUE)->count();
            $data['total_revenue'] = Invoice::sum('amount');
        }

        if ($user->hasRole('pac_member')) {
            $data['pending_reviews'] = Requirement::where('status', Requirement::STATUS_SENT_TO_PAC)->count();
        }

        if ($user->hasRole('faculty')) {
            $data['investigator_projects'] = Project::where('project_investigator_id', $user->id)->count();
            $data['active_research'] = Project::where('project_investigator_id', $user->id)
                ->where('status', Project::STATUS_ONGOING)
                ->count();
        }

        return view('pms.dashboard.index1', compact('data', 'user'),['pageConfigs'=> $pageConfigs]);
    }
}
