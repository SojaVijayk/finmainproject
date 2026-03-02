<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Timesheet;
use App\Models\PMS\TimesheetCategory;
use App\Models\User;
use App\Models\PMS\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $date = $request->has('date')
            ? Carbon::parse($request->date)
            : now();


            $startOfWeek = $date->copy()->startOfWeek();
$weekDays = collect();

for ($i = 0; $i < 7; $i++) {
    $weekdate = $startOfWeek->copy()->addDays($i);
    $weekDays->push([
        'date' => $weekdate,
        'dayName' => $weekdate->format('D'),
        'isToday' => $weekdate->isToday(),
        'isWeekend' => $weekdate->isWeekend(),
    ]);
}

        $timesheets = Timesheet::where('user_id', auth()->id())
            ->whereDate('date', $date)
            ->with(['category', 'project','items'])
            ->get();

        $categories = TimesheetCategory::whereHas('users', function($query) {
            $query->where('user_id', auth()->id());
        })->orWhere('is_system', true)
        ->get();

        $projects = Project::whereHas('teamMembers', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->with(['requirement'])
        ->where('status', Project::STATUS_ONGOING)
        ->get();


            //  echo $date;exit;
        return view('pms.timesheets.index', [
            'timesheets' => $timesheets,
            'categories' => $categories,
            'projects' => $projects,
            'selectedDate' => $date,
             'weekDays' => $weekDays,
            'pageConfigs'=> $pageConfigs
        ]);
    }

    public function calendar(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $month = $request->has('month')
            ? Carbon::parse($request->month)
            : now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $timesheets = Timesheet::where('user_id', auth()->id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with(['category', 'project'])
            ->get()
            ->groupBy('date');

        return view('pms.timesheets.calendar', [
            'timesheets' => $timesheets,
            'currentMonth' => $month,
             'pageConfigs'=> $pageConfigs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|exists:timesheet_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:1000',
        ]);

        // Convert hours to minutes
        $minutes = $request->hours * 60;

        // Check for existing entry for same date, category and project
        $existing = Timesheet::where('user_id', auth()->id())
            ->whereDate('date', $request->date)
            ->where('category_id', $request->category_id)
            ->where('project_id', $request->project_id)
            ->first();

        if ($existing) {
            $existing->update([
                'minutes' => $existing->minutes + $minutes,
                'description' => $request->description ?? $existing->description,
            ]);

            return redirect()->back()
                ->with('success', 'Timesheet entry updated successfully.');
        }

        Timesheet::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'category_id' => $request->category_id,
            'project_id' => $request->project_id,
            'minutes' => $minutes,
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Timesheet entry added successfully.');
    }

//     public function bulkStore(Request $request)
// {
//     $validated = $request->validate([
//        'entries.*.date' => 'required|date',
//     'entries.*.category_id' => 'required_without:entries.*.project_id|nullable|exists:timesheet_categories,id',
//     'entries.*.project_id' => 'required_without:entries.*.category_id|nullable|exists:projects,id',
//     'entries.*.hours' => 'required|numeric|min:0.1|max:24',
//     ]);


//     foreach ($request->entries as $entry) {
//         Timesheet::updateOrCreate(
//             [
//                 'user_id' => auth()->id(),
//                 'date' => $entry['date'],
//                 'category_id' => $entry['category_id'],
//                 'project_id' => $entry['project_id'] ?? null,
//             ],
//             ['minutes' => $entry['hours']*60]
//         );
//     }

//     return response()->json(['success' => true]);
// }
    public function bulkStore(Request $request)
{
    $validated = $request->validate([
       'entries.*.date' => 'required|date',
    'entries.*.category_id' => 'required_without:entries.*.project_id|nullable|exists:timesheet_categories,id',
    'entries.*.project_id' => 'required_without:entries.*.category_id|nullable|exists:projects,id',
    'entries.*.hours' => 'required|numeric|min:0.1|max:24',
     'entries.*.items' => 'array|nullable',
        'entries.*.items.*.item_name' => 'required_with:entries.*.items|string|max:255',
        'entries.*.items.*.hours' => 'required_with:entries.*.items|numeric|min:0.1|max:24',
    ]);

      $userId = auth()->id();

    // Extract dates from incoming entries
    $dates = collect($request->entries)->pluck('date')->unique();

    /**
     * 🔥 DELETE existing entries for only these dates
     * Related `timesheet_items` will also be deleted because of FK cascade or manually below
     */
    $timesheetsToDelete = Timesheet::where('user_id', $userId)
        ->whereIn('date', $dates)
        ->get();

    foreach ($timesheetsToDelete as $ts) {
        // delete child items
        $ts->items()->delete();
        // delete the parent timesheet
        $ts->delete();
    }
    foreach ($request->entries as $entry) {
        $timesheet= Timesheet::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'date' => $entry['date'],
                'category_id' => $entry['category_id'],
                'project_id' => $entry['project_id'] ?? null,
            ],
            ['minutes' => $entry['hours']*60]
        );

          // If “Others” category has items, store split-up
        $timesheet->items()->delete();
        if (!empty($entry['items'])) {
            $timesheet->items()->delete(); // reset previous
            foreach ($entry['items'] as $item) {
                $timesheet->items()->create([
                    'item_name' => $item['item_name'],
                    'hours' => $item['hours'],
                    'description' => $item['description'] ?? null,
                ]);
            }
        }


    }

    return response()->json(['success' => true]);
}
    public function update(Request $request, Timesheet $timesheet)
    {
        if ($timesheet->user_id != auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|exists:timesheet_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:1000',
        ]);

        $timesheet->update([
            'date' => $request->date,
            'category_id' => $request->category_id,
            'project_id' => $request->project_id,
            'minutes' => $request->hours * 60,
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Timesheet entry updated successfully.');
    }

    public function destroy(Timesheet $timesheet)
    {
        if ($timesheet->user_id != auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
         $timesheet->items()->delete();
        $timesheet->delete();

        return redirect()->back()
            ->with('success', 'Timesheet entry deleted successfully.');
    }

    public function destroyItem(Timesheet $timesheet, $itemId)
{
    if ($timesheet->user_id != auth()->id()) {
        abort(403, 'Unauthorized action.');
    }

    $item = $timesheet->items()->find($itemId);
    if (!$item) {
        return redirect()->back()->with('error', 'Item not found.');
    }

    $item->delete();

    // After deleting item, if no more items remain — auto delete timesheet
    if ($timesheet->items()->count() === 0) {
        $timesheet->delete();
        return redirect()->back()->with('success', 'Item deleted. No items left, so timesheet removed automatically.');
    }

    return redirect()->back()->with('success', 'Item deleted successfully.');
}

    public function report(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $userId = $request->has('user_id') && auth()->user()->can('view_all_timesheets')
            ? $request->user_id
            : auth()->id();

        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'project'])
            ->get();

        $groupedTimesheets = $timesheets->groupBy(['user_id', function($item) {
            return $item->date->format('Y-m-d');
        }]);

        $users = auth()->user()->can('view_all_timesheets')
            ? User::whereHas('timesheets')->get()
            : collect([auth()->user()]);

        $categories = TimesheetCategory::whereHas('timesheets', function($query) use ($userId, $startDate, $endDate) {
            $query->where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate]);
        })->get();

        return view('pms.timesheets.report', [
            'timesheets' => $timesheets,
            'groupedTimesheets' => $groupedTimesheets,
            'categories' => $categories,
            'users' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedUserId' => $userId,
             'pageConfigs'=> $pageConfigs
        ]);
    }

    public function resourceUtilization(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $query = Timesheet::query();
        $query->whereBetween('date', [$startDate, $endDate]);

        if (!$request->user()->can('view_all_timesheets')) {
            $query->where('user_id', auth()->id());
        }

        $timesheets = $query->with(['user', 'project'])->get();

        // Calculate working days
        $workingDays = $this->getWorkingDaysCount($startDate, $endDate);
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

        $users = auth()->user()->can('view_all_timesheets')
            ? User::whereHas('timesheets')->get()
            : collect([auth()->user()]);

        return view('pms.timesheets.resource-utilization', [
            'utilizationData' => $utilizationData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'workingDays' => $workingDays,
            'users' => $users,
             'pageConfigs'=> $pageConfigs
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

    private function getWorkingDaysCount($startDate, $endDate)
    {
        $days = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if ($current->isWeekday()) { // Monday to Friday
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}