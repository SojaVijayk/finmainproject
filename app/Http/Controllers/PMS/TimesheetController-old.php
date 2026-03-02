<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\TimesheetStoreRequest;
use App\Http\Requests\PMS\TimesheetUpdateRequest;
use App\Models\PMS\Project;
use App\Models\PMS\Timesheet;
use App\Models\PMS\TimesheetCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TimesheetControllerold extends Controller
{
    public function index(Request $request)
    {
        $date = $request->has('date')
            ? Carbon::parse($request->date)
            : now();

        $timesheets = Timesheet::where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->with(['category', 'project'])
            ->get();

        $categories = TimesheetCategory::whereHas('users', function($query) {
            $query->where('user_id', Auth::id());
        })->orWhere('is_system', true)
        ->get();

        $projects = Project::whereHas('teamMembers', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->where('status', Project::STATUS_ONGOING)
        ->get();

        return view('pms.timesheets.index', [
            'timesheets' => $timesheets,
            'categories' => $categories,
            'projects' => $projects,
            'selectedDate' => $date,
        ]);
    }

    public function calendar(Request $request)
    {
        $month = $request->has('month')
            ? Carbon::parse($request->month)
            : now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $timesheets = Timesheet::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with(['category', 'project'])
            ->get()
            ->groupBy('date');

        return view('pms.timesheets.calendar', [
            'timesheets' => $timesheets,
            'currentMonth' => $month,
        ]);
    }

    public function store(TimesheetStoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['minutes'] = $data['hours'] * 60; // Convert hours to minutes

        // Check for existing entry for same date, category and project
        $existing = Timesheet::where('user_id', Auth::id())
            ->whereDate('date', $data['date'])
            ->where('category_id', $data['category_id'])
            ->where('project_id', $data['project_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'minutes' => $existing->minutes + $data['minutes'],
                'description' => $data['description'] ?? $existing->description,
            ]);
        } else {
            Timesheet::create($data);
        }

        return redirect()->back()
            ->with('success', 'Timesheet entry added successfully.');
    }

    public function update(TimesheetUpdateRequest $request, Timesheet $timesheet)
    {
        if ($timesheet->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validated();
        $data['minutes'] = $data['hours'] * 60; // Convert hours to minutes

        $timesheet->update($data);

        return redirect()->back()
            ->with('success', 'Timesheet entry updated successfully.');
    }

    public function destroy(Timesheet $timesheet)
    {
        if ($timesheet->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $timesheet->delete();

        return redirect()->back()
            ->with('success', 'Timesheet entry deleted successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $userId = $request->has('user_id') && Auth::user()->can('view_all_timesheets')
            ? $request->user_id
            : Auth::id();

        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'project'])
            ->get()
            ->groupBy(['category_id', function($item) {
                return $item->date->format('Y-m-d');
            }]);

        $categories = TimesheetCategory::whereHas('timesheets', function($query) use ($userId, $startDate, $endDate) {
            $query->where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate]);
        })->get();

        $users = Auth::user()->can('view_all_timesheets')
            ? User::whereHas('timesheets')->get()
            : collect([Auth::user()]);
        $groupedTimesheets = $timesheets->groupBy([
        'user_id',
        'category_id'
    ]);
        return view('pms.timesheets.report', [
            'timesheets' => $timesheets,
            'groupedTimesheets' => $groupedTimesheets,
            'categories' => $categories,
            'users' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedUserId' => $userId,
        ]);
    }
    public function reportopd(Request $request)
{
    $startDate = $request->has('start_date')
        ? Carbon::parse($request->start_date)
        : now()->startOfMonth();

    $endDate = $request->has('end_date')
        ? Carbon::parse($request->end_date)
        : now()->endOfMonth();

    $userId = $request->has('user_id') && Auth::user()->can('view_all_timesheets')
        ? $request->user_id
        : Auth::id();

    // Get all timesheets with relations
    $query = Timesheet::with(['category', 'user'])
        ->whereBetween('date', [$startDate, $endDate]);

    if ($userId !== 'all') {
        $query->where('user_id', $userId);
    }

    $timesheets = $query->get();

    // Group by user_id → category_id
    $groupedTimesheets = $timesheets->groupBy([
        'user_id',
        'category_id'
    ]);

    // Get users who have timesheets
    $users = Auth::user()->can('view_all_timesheets')
        ? User::whereHas('timesheets')->get()
        : collect([Auth::user()]);

    return view('pms.timesheets.report', [
        'groupedTimesheets' => $groupedTimesheets,
        'users' => $users,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'selectedUserId' => $userId,
    ]);
}

    public function export(Request $request)
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $userId = $request->has('user_id') && Auth::user()->can('view_all_timesheets')
            ? $request->user_id
            : Auth::id();

        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'project'])
            ->get();

        // TODO: Implement export to Excel or PDF
        // You can use Laravel Excel package for this

        return redirect()->back()
            ->with('success', 'Export functionality will be implemented soon.');
    }
    public function getProjects(Request $request)
{
    $categoryId = $request->input('category_id');
    $userId = auth()->id();

    $projects = Project::query()
        ->whereHas('teamMembers', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->when($categoryId, function($query) use ($categoryId) {
            $query->whereHas('requirement', function($q) use ($categoryId) {
                $q->where('project_category_id', $categoryId);
            });
        })
        ->where('status', Project::STATUS_ONGOING)
        ->get(['id', 'title']);

    return response()->json($projects);
}


}
