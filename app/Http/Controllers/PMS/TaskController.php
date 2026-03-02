<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\TaskStoreRequest;
use App\Http\Requests\PMS\TaskUpdateRequest;
use App\Models\PMS\Milestone;
use App\Models\PMS\Project;
use App\Models\PMS\Task;
use App\Models\PMS\TaskAssignment;
use App\Models\User;
use App\Models\PMS\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Milestone $milestone)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $tasks = $milestone->tasks()
            ->with(['assignments.user'])
            ->orderBy('priority', 'desc')
            ->orderBy('start_date')
            ->get();

        return view('pms.tasks.index', compact('milestone', 'tasks'),['pageConfigs'=> $pageConfigs]);
    }

    public function create(Project $project, Milestone $milestone)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if ($milestone->status == Milestone::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Cannot add tasks to a completed milestone.');
        }

        $teamMembers = $milestone->project->teamMembers()->with('user')->get();

        return view('pms.tasks.create', compact('milestone', 'teamMembers','project'),['pageConfigs'=> $pageConfigs]);
    }

    public function store(TaskStoreRequest $request, Project $project, Milestone $milestone)
    {
        if ($milestone->status == Milestone::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Cannot add tasks to a completed milestone.');
        }

        $data = $request->validated();
        $task = $milestone->tasks()->create($data);

        // Assign team members
        if ($request->has('assigned_to')) {
            foreach ($request->assigned_to as $userId) {
                $task->assignments()->create([
                    'user_id' => $userId,
                ]);
            }
        }

        return redirect()->route('pms.tasks.show', [$milestone->project->id, $milestone->id, $task->id])
            ->with('success', 'Task created successfully.');
    }

    public function show(Project $project, Milestone $milestone, Task $task)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $task->load(['assignments.user', 'milestone']);

        return view('pms.tasks.show', compact('project', 'milestone', 'task'),['pageConfigs'=> $pageConfigs]);
    }

    public function edit(Project $project, Milestone $milestone, Task $task)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        if ($task->status == Task::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Completed task cannot be edited.');
        }

        $teamMembers = $milestone->project->teamMembers()->with('user')->get();
        $assignedUserIds = $task->assignments->pluck('user_id')->toArray();

        return view('pms.tasks.edit', compact('project', 'milestone', 'task', 'teamMembers', 'assignedUserIds'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(TaskUpdateRequest $request, Project $project, Milestone $milestone, Task $task)
    {
        if ($task->status == Task::STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Completed task cannot be edited.');
        }

        $data = $request->validated();
        $task->update($data);

        // Update assignments
        $task->assignments()->delete();
        if ($request->has('assigned_to')) {
            foreach ($request->assigned_to as $userId) {
                $task->assignments()->create([
                    'user_id' => $userId,
                ]);
            }
        }

        return redirect()->route('pms.tasks.show', [$project->id, $milestone->id, $task->id])
            ->with('success', 'Task updated successfully.');
    }

    public function start(Project $project, Milestone $milestone, Task $task)
    {
        if ($task->status != Task::STATUS_NOT_STARTED) {
            return redirect()->back()
                ->with('error', 'Task cannot be started in its current status.');
        }



        $task->update([
            'status' => Task::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Task started successfully.');
    }

    public function complete(Project $project, Milestone $milestone, Task $task)
    {
        if ($task->status != Task::STATUS_IN_PROGRESS) {
            return redirect()->back()
                ->with('error', 'Task cannot be completed in its current status.');
        }

        if ($task->started_at) {
            $total_minutes = $task->started_at->diffInMinutes(now());
        }


        $task->update([
            'status' => Task::STATUS_COMPLETED,
            'completed_at' => now(),
            'total_minutes'=>  $total_minutes
        ]);

        return redirect()->back()
            ->with('success', 'Task marked as completed successfully.');
    }

//     public function kanban(Project $project)
// {
//   $pageConfigs = ['myLayout' => 'horizontal'];
//     $tasks = $project->tasks()
//         ->with(['assignments.user', 'comments.user', 'milestone'])
//         ->orderBy('priority', 'desc')
//         ->orderBy('end_date')
//         ->get()
//         ->groupBy('status');

//     return view('pms.tasks.kanban', compact('project', 'tasks'),['pageConfigs'=> $pageConfigs]);
// }

// public function updateStatus(Request $request, Task $task)
// {
//     $request->validate([
//         'status' => 'required|integer'
//     ]);

//     $task->update(['status' => $request->status]);

//     return response()->json(['success' => true, 'message' => 'Task moved successfully.']);
// }

public function kanban(Project $project)
{
    $milestones = $project->milestones()->get();
    $priorities = ['Low', 'Medium', 'High', 'Critical'];
    $pageConfigs = ['myLayout' => 'horizontal'];
  $user =Auth::user();
    $tasks = $project->tasks()->whereHas('assignments', function($q) use ($user) {
          $q->where('user_id', $user->id);
          })
        ->with(['milestone'])
        ->get()
        ->groupBy(fn ($task) => match ($task->status) {
            0 => 'todo',
            1 => 'start',
            2 => 'complete',
            default => 'todo',
        });

    return view('pms.tasks.kanban', compact('project', 'milestones', 'priorities', 'tasks'),['pageConfigs'=> $pageConfigs]);
}

public function kanbanData(Project $project, Request $request)
{
    $query = $project->tasks()->with('milestone');

    // Filters
    if ($request->filled('milestone_id')) {
        $query->where('milestone_id', $request->milestone_id);
    }
    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }
    if ($request->filled('due')) {
        if ($request->due === 'today') {
            $query->whereDate('tasks.end_date', today());
        } elseif ($request->due === 'week') {
            $query->whereBetween('tasks.end_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($request->due === 'overdue') {
            $query->where('tasks.end_date', '<', now());
        }
    }

    $tasks = $query->get()->groupBy(fn ($task) => match ($task->status) {
        0 => 'todo',
        1 => 'start',
        2 => 'complete',
        default => 'todo',
    });

    return response()->json(['tasks' => $tasks]);
}

public function updateStatus(Request $request,Project $project, Task $task)
{
    $map = [
        'todo' => 0,
        'start' => 1,
        'complete' => 2,
    ];

    $request->validate(['status' => 'required|string']);
    $now = now();
    $status = $request->input('status');
    if ($status === 'start' ) {
        $task->started_at = $now;
          $task->total_minutes = NULL;
          $task->completed_at = NULL;
    }

    if ($status === 'complete' ) {
        $task->completed_at = $now;
        if ($task->started_at) {
            $task->total_minutes = $task->started_at->diffInMinutes($now);
        }
    }
     $task->status = $map[$request->status] ?? 0;
    $task->save();

    // $task->update(['status' => $map[$request->status] ?? 0]);

    return response()->json(['success' => true]);
}

public function comments(Project $project,Task $task)
{
    return response()->json(
        $task->comments()
             ->with('user:id,name')
             ->latest()
             ->get()
             ->map(function($c) {
                 return [
                     'id' => $c->id,
                     'comment' => $c->comment,
                     'user' => $c->user,
                    'created_at' => $c->created_at ? $c->created_at->diffForHumans() : '',
                     'updated_at' => $c->updated_at ? $c->updated_at->diffForHumans() : '',
                 ];
             })
    );
}

public function addComment(Request $request,Project $project, Task $task)
{
    $request->validate([
        'comment' => 'required|string|max:2000',
    ]);

    $comment = $task->comments()->create([
        'comment' => $request->comment,
        'user_id' => Auth::id(),
    ]);

    return response()->json([
        'id' => $comment->id,
        'comment' => $comment->comment,
        'user' => $comment->user()->select('id','name')->first(),
       'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
                     'updated_at' => $comment->updated_at ? $comment->updated_at->diffForHumans() : '',
    ]);
}

public function updateComment(Request $request,Project $project,  Task $task, TaskComment $comment)
{
    if ($comment->user_id !== Auth::id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $request->validate([
        'comment' => 'required|string|max:2000',
    ]);

    $comment->update(['comment' => $request->comment]);

    return response()->json([
        'id' => $comment->id,
        'comment' => $comment->comment,
        'user' => $comment->user()->select('id','name')->first(),
         'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
                     'updated_at' => $comment->updated_at ? $comment->updated_at->diffForHumans() : '',
    ]);
}



}
