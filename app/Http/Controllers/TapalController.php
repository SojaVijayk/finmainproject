<?php

namespace App\Http\Controllers;

use App\Models\Tapal;
use App\Models\TapalMovement;
use App\Models\TapalAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TapalAssigned;
use App\Mail\TapalNotification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB;

class TapalController extends Controller
{
    public function index(Request $request)

    {
       $pageConfigs = ['myLayout' => 'horizontal'];
      $user = auth()->user();

      $search = $request->input('search');
    $status = $request->input('status');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');

       $stats = $this->getTapalStatistics($user);


          $query = Tapal::with(['creator', 'currentHolder'])
            ->where('created_by', Auth::id())
            ->orWhereHas('movements', function($query) {
                $query->where('to_user_id', Auth::id());
            });


    // Apply filters
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('tapal_number', 'like', "%$search%")
              ->orWhere('subject', 'like', "%$search%")
              ->orWhere('ref_number', 'like', "%{$search}%")
              ->orWhere('from_name', 'like', "%{$search}%")
              ->orWhere('from_address', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }


    if ($fromDate && $fromDate != '') {
        $query->whereDate('created_at', '>=', $fromDate);
    }

    if ($toDate && $toDate != '') {
        $query->whereDate('created_at', '<=', $toDate);
    }

    $tapals = $query->orderBy('created_at', 'desc')->paginate(25);



        // $query = Tapal::with(['creator', 'currentHolder'])
        //     ->where('created_by', Auth::id())
        //     ->orWhereHas('movements', function($query) {
        //         $query->where('to_user_id', Auth::id());
        //     })
        //     ->orderBy('created_at', 'desc');
        //      $tapals = $query->latest()->paginate(25);

        return view('tapals.index', compact('tapals','stats'),['pageConfigs'=> $pageConfigs]);
    }

    public function create()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $users = User::where('active', 1)->orderBy('name')->get();
        return view('tapals.create', compact('users'),['pageConfigs'=> $pageConfigs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:inward,outward',
            'inward_date' => 'required|date',
            'received_date' => 'required|date',
            'inward_mode' => 'required|in:Speed Post,Post,Courier,By Mail,By Hand',
            'mail_id' => 'nullable|required_if:inward_mode,By Mail|email',
            'from_name' => 'nullable|required_unless:inward_mode,By Mail|string|max:255',
            'from_address' => 'nullable|required_unless:inward_mode,By Mail|string',
            'from_department' => 'nullable|string|max:255',
            'from_mobile' => 'nullable|string|max:20',
            'from_person_details' => 'nullable|string',
            'ref_number' => 'nullable|string|max:1000',
            'letter_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'assigned_user_id' => 'required|exists:users,id',
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
            'assignment_remarks' => 'nullable|string',
        ]);

        // Create tapal
        $tapal = Tapal::create([
            'type' => $validated['type'],
            'inward_date' => $validated['inward_date'],
            'received_date' => $validated['received_date'],
            'inward_mode' => $validated['inward_mode'],
            'mail_id' => $validated['mail_id'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
            'from_address' => $validated['from_address'] ?? null,
            'from_department' => $validated['from_department'] ?? null,
            'from_mobile' => $validated['from_mobile'] ?? null,
            'from_person_details' => $validated['from_person_details'] ?? null,
            'ref_number' => $validated['ref_number'] ?? null,
            'letter_date' => $validated['letter_date'] ?? null,
            'subject' => $validated['subject'],
            'description' => $validated['description'] ?? null,
            // 'current_holder_id' => $validated['assigned_user_id'],
            'created_by' => Auth::id(),
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/tapal_attachments');

                TapalAttachment::create([
                    'tapal_id' => $tapal->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Create movement for assigned user
        // $movement = TapalMovement::create([
        //     'tapal_id' => $tapal->id,
        //     'from_user_id' => Auth::id(),
        //     'to_user_id' => $validated['assigned_user_id'],
        //     'remarks' => $validated['assignment_remarks'] ?? null,
        //     'is_assignment' => true,
        //     'status' => 'Pending',
        // ]);

          $assignedUsers = (array)$validated['assigned_user_id'];

    foreach ($assignedUsers as $userId) {
    $movement = TapalMovement::create([
        'tapal_id' => $tapal->id,
        'from_user_id' => Auth::id(),
        'to_user_id' => $userId,
        'remarks' => $validated['assignment_remarks'] ?? null,
        'is_assignment' => true,
        'is_primary' => true, // All are marked as potential primary
        'status' => 'Pending',
    ]);

    $assignedUser = User::find($userId);
    Mail::to($assignedUser->email)->cc('mail@cmd.kerala.gov.in')->send(new TapalAssigned($tapal, $movement, Auth::user()));
  }

        // Send email to assigned user
        // $assignedUser = User::find($validated['assigned_user_id']);
        // Mail::to($assignedUser->email)->cc('mail@cmd.kerala.gov.in')->send(new TapalAssigned($tapal, $movement, Auth::user()));



        // Handle notification emails
        foreach ($validated['notify_users'] ?? [] as $userId) {
            if ($userId != $validated['assigned_user_id']) {
                $notification = TapalMovement::create([
                    'tapal_id' => $tapal->id,
                    'from_user_id' => Auth::id(),
                    'to_user_id' => $userId,
                    'remarks' => 'For your information',
                    'is_assignment' => false,
                    'status' => 'Notified',
                ]);

                $notifyUser = User::find($userId);
                 Mail::to($notifyUser->email)->send(new TapalNotification($tapal, $notification, Auth::user()));
            }
        }

        return redirect()->route('tapals.show', $tapal->id)
            ->with('success', 'Tapal created and assigned successfully!');
    }

    public function show(Tapal $tapal)
    {
        $this->authorize('view', $tapal);
        $pageConfigs = ['myLayout' => 'horizontal'];
        $tapal->load(['attachments', 'movements.fromUser', 'movements.toUser', 'creator', 'currentHolder']);
        $users = User::where('id', '!=', Auth::id())->where('active', 1)->orderBy('name')->get();
        return view('tapals.show', compact('tapal', 'users'),['pageConfigs'=> $pageConfigs]);
    }

    public function edit(Tapal $tapal)
    {$pageConfigs = ['myLayout' => 'horizontal'];
        $this->authorize('update', $tapal);

        $users = User::where('id', '!=', Auth::id())->where('active', 1)->orderBy('name')->get();
        return view('tapals.edit', compact('tapal', 'users'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(Request $request, Tapal $tapal)
    {
        $this->authorize('update', $tapal);

        $validated = $request->validate([
            'inward_date' => 'required|date',
            'received_date' => 'required|date',
            'inward_mode' => 'required|in:Speed Post,Post,Courier,By Mail,By Hand',
            'mail_id' => 'nullable|required_if:inward_mode,By Mail|email',
            'from_name' => 'nullable|required_unless:inward_mode,By Mail|string|max:255',
            'from_address' => 'nullable|required_unless:inward_mode,By Mail|string',
            'from_department' => 'nullable|string|max:255',
            'from_mobile' => 'nullable|string|max:20',
            'from_person_details' => 'nullable|string',
            'ref_number' => 'nullable|string|max:100',
            'letter_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $tapal->update($validated);

        // Handle new attachments
    if ($request->hasFile('new_attachments')) {
        foreach ($request->file('new_attachments') as $file) {
            $path = $file->store('public/tapal_attachments');

            TapalAttachment::create([
                'tapal_id' => $tapal->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

        return redirect()->route('tapals.show', $tapal->id)
            ->with('success', 'Tapal updated successfully!');
    }

    public function destroy(Tapal $tapal)
    {
        $this->authorize('delete', $tapal);

        $tapal->delete();
        return redirect()->route('tapals.index')
            ->with('success', 'Tapal deleted successfully!');
    }

    public function forward(Request $request, Tapal $tapal)
    {
        $this->authorize('forward', $tapal);

        $validated = $request->validate([
            'assigned_user_id' => 'required|exists:users,id',
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
            'assignment_remarks' => 'nullable|string',
        ]);

        // Update current holder
        $tapal->update(['current_holder_id' => $validated['assigned_user_id']]);
        TapalMovement::where('tapal_id', $tapal->id)->update([
          'status' => 'Notified',
          'is_primary' => false,
          'is_assignment' => false,
        ]);

        $assignedUsers = (array)$validated['assigned_user_id'];

    foreach ($assignedUsers as $userId) {
    $movement = TapalMovement::create([
        'tapal_id' => $tapal->id,
        'from_user_id' => Auth::id(),
        'to_user_id' => $userId,
        'remarks' => $validated['assignment_remarks'] ?? null,
        'is_assignment' => true,
        'is_primary' => true, // All are marked as potential primary
        'status' => 'Pending',
    ]);

    $assignedUser = User::find($userId);
    Mail::to($assignedUser->email)->cc('mail@cmd.kerala.gov.in')->send(new TapalAssigned($tapal, $movement, Auth::user()));
    }


        // Handle notification emails
        foreach ($validated['notify_users'] ?? [] as $userId) {
            if ($userId != $validated['assigned_user_id']) {
                $notification = TapalMovement::create([
                    'tapal_id' => $tapal->id,
                    'from_user_id' => Auth::id(),
                    'to_user_id' => $userId,
                    'remarks' => 'For your information',
                    'is_assignment' => false,
                    'status' => 'Notified',
                ]);

                $notifyUser = User::find($userId);
                 Mail::to($notifyUser->email)->send(new TapalNotification($tapal, $notification, Auth::user()));
            }
        }

        return redirect()->route('tapals.show', $tapal->id)
            ->with('success', 'Tapal forwarded successfully!');
    }

    public function share(Request $request, Tapal $tapal)
    {
        $this->authorize('forward', $tapal);

        $validated = $request->validate([
            'share_users' => 'nullable|array',
            'share_users.*' => 'exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $userAssignments = $tapal->movements()
        ->where('is_assignment', true)
        ->get();

        // Handle notification emails
        foreach ($validated['share_users'] ?? [] as $userId) {
             $alreadyAssigned = $userAssignments->contains('to_user_id', $userId);
               if (!$alreadyAssigned) {
            // if ($userId != $validated['assigned_user_id']) {
                $notification = TapalMovement::create([
                    'tapal_id' => $tapal->id,
                    'from_user_id' => Auth::id(),
                    'to_user_id' => $userId,
                    'remarks' => $validated['remarks'],
                    'is_assignment' => false,
                    'status' => 'Notified',
                ]);

                $notifyUser = User::find($userId);
                 Mail::to($notifyUser->email)->send(new TapalNotification($tapal, $notification, Auth::user()));
            }
        }

        return redirect()->route('tapals.show', $tapal->id)
            ->with('success', 'Tapal forwarded successfully!');
    }



    public function accept(TapalMovement $movement)
{
    $this->authorize('accept', $movement);

    // Check if someone else already accepted
    $alreadyAccepted = TapalMovement::where('tapal_id', $movement->tapal_id)
        ->where('is_accepted', true)
        ->exists();

    if ($alreadyAccepted) {
        // Mark this as secondary assignment
        $movement->update([
            'status' => 'Notified',
            'is_primary' => false,
            'accepted_at' => now()
        ]);

        return redirect()->back()
            ->with('info', 'This tapal has already been accepted by another user. You have view-only access.');
    }

    // This is the first acceptance
    DB::transaction(function() use ($movement) {
        // Update the movement
        $movement->update([
            'status' => 'Accepted',
            'is_accepted' => true,
            'accepted_at' => now()
        ]);

        // Update the tapal's current holder
        $movement->tapal()->update([
            'current_holder_id' => $movement->to_user_id
        ]);

        // Mark all other assignments as non-primary
        TapalMovement::where('tapal_id', $movement->tapal_id)
            ->where('id', '!=', $movement->id)
            ->update(['is_primary' => false, 'status' => 'Notified', 'is_assignment' => false]);
    });

    return redirect()->route('tapals.show', $movement->tapal_id)
        ->with('success', 'Tapal accepted successfully! You are now the primary holder.');
}

    public function complete(Request $request, TapalMovement $movement)
    {
         $this->authorize('complete', $movement);
         // Double check the movement belongs to current user
    if ($movement->to_user_id !== Auth::id()) {
        abort(403, 'Unauthorized action');
    }

    // Check if already completed
    if ($movement->status === 'Completed') {
        return redirect()->back()
            ->with('error', 'This tapal is already completed');
    }

    $validated = $request->validate([
        'completion_remarks' => 'required|string'
    ]);

    $movement->update([
        'status' => 'Completed',
        'completed_at' => now(),
        'remarks' => $validated['completion_remarks'] // Store completion remarks
    ]);

    return redirect()->route('tapals.show', $movement->tapal_id)
        ->with('success', 'Tapal marked as completed!');
    }

    public function tracingOLD(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        $query = Tapal::query()->with(['creator', 'currentHolder', 'movements.fromUser', 'movements.toUser']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('tapal_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('ref_number', 'like', "%{$search}%")
                  ->orWhere('from_name', 'like', "%{$search}%")
                  ->orWhere('from_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('from_date') && $request->input('from_date') != '') {
            $query->whereDate('inward_date', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date') && $request->input('to_date') != '') {
            $query->whereDate('inward_date', '<=', $request->input('to_date'));
        }

        $tapals = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('tapals.tracing', compact('tapals'),['pageConfigs'=> $pageConfigs]);
    }
    public function tracing1(Request $request)

    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        // Get filter parameters
        $search = $request->input('search');
        $userId = $request->input('user_id');
        $status = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get statistics data
        $stats = $this->getTracingStatistics($request);

        // Get users for filter dropdown
        $users = User::where('active', 1)->orderBy('name')->get();

        // Query for tapals

        $query = Tapal::with(['creator', 'currentHolder', 'movements.fromUser', 'movements.toUser']);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('tapal_number', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%")
                   ->orWhere('ref_number', 'like', "%{$search}%")
                  ->orWhere('from_name', 'like', "%{$search}%")
                  ->orWhere('from_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('movements', function($q) use ($userId) {
                      $q->where('to_user_id', $userId)
                      ->where('is_assignment', 1);
                  });
            });
        }

        if ($status) {
            if ($status == 'Overdue') {
                $query->whereHas('movements', function($q) {
                    // $q->where('deadline', '<', now())
                      $q->where(function($q) {
                          $q->where('status', 'like', '%pending%')
                            ->orWhere('status', 'like', '%Accepted%');
                      });
                });
            } else {
                $query->whereHas('movements', function($q) use ($status) {
                    $q->where('status', 'like', "%$status%");
                });
            }
        }

        if ($fromDate && $fromDate!='') {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate && $toDate!='') {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $tapals = $query->latest()->paginate(25);

        return view('tapals.tracing', compact('tapals', 'stats', 'users'),['pageConfigs'=> $pageConfigs]);
    }

    protected function getTracingStatistics1(Request $request)
    {
        // Base query for all tapals (can be filtered by date range)
        $baseQuery = Tapal::query();

        if ($request->has('from_date') && $request->input('from_date') != '') {
            $baseQuery->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date') && $request->input('to_date') != '') {
            $baseQuery->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Total counts
        $totalTapals = $baseQuery->count();
        $totalPending = $this->getStatusCount($baseQuery, 'pending');
        $totalCompleted = $this->getStatusCount($baseQuery, 'completed');
        $totalInProgress = $this->getStatusCount($baseQuery, 'in progress');
        $totalOverdue = $this->getOverdueCountTracing($baseQuery);

        // Percentage calculations
        $pendingPercentage = $totalTapals > 0 ? round(($totalPending / $totalTapals) * 100, 2) : 0;
        $completedPercentage = $totalTapals > 0 ? round(($totalCompleted / $totalTapals) * 100, 2) : 0;
        $overduePercentage = $totalPending > 0 ? round(($totalOverdue / $totalPending) * 100, 2) : 0;

        // Top users by performance
        // $topUsers = User::withCount([
        //     'tapalMovements as pending_count' => function($query) {
        //         $query->where(function($q) {
        //             $q->where('status', 'like', '%pending%')
        //               ->orWhere('status', 'like', '%in progress%');
        //         });
        //     },
        //     'tapalMovements as completed_count' => function($query) {
        //         $query->where('status', 'like', '%completed%');
        //     }
        // ])->orderByDesc('completed_count')
        //   ->limit(5)
        //   ->get();


        return [
            'total_tapals' => $totalTapals,
            'total_pending' => $totalPending,
            'total_completed' => $totalCompleted,
            'total_in_progress' => $totalInProgress,
            'total_overdue' => $totalOverdue,
            'pending_percentage' => $pendingPercentage,
            'completed_percentage' => $completedPercentage,
            'overdue_percentage' => $overduePercentage

        ];
    }

    protected function getStatusCount1($query, $status)
    {
        $clone = clone $query;
        return $clone->whereHas('movements', function($q) use ($status) {
            $q->where('status', 'like', "%$status%");
        })->count();
    }

    public function tracing(Request $request)
{
    $pageConfigs = ['myLayout' => 'horizontal'];

    // Get filter parameters
    $search = $request->input('search');
    $userId = $request->input('user_id');
    $status = $request->input('status');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');

    // Get statistics data
    $stats = $this->getTracingStatistics($request);

    // Get users for filter dropdown
    $users = User::where('active', 1)->orderBy('name')->get();

    // Query for tapals
    $query = Tapal::with(['creator', 'movements.fromUser', 'movements.toUser']);

    // Apply filters
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('tapal_number', 'like', "%$search%")
              ->orWhere('subject', 'like', "%$search%")
              ->orWhere('ref_number', 'like', "%{$search}%")
              ->orWhere('from_name', 'like', "%{$search}%")
              ->orWhere('from_address', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($userId) {
        $query->where(function($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhereHas('movements', function($q) use ($userId) {
                  $q->where('to_user_id', $userId)
                    ->where('is_assignment', true);
              });
        });
    }

    if ($status) {
        if ($status == 'Overdue') {
            $query->whereHas('movements', function($q) {
                $q->where(function($q) {
                    $q->where('status', 'Pending')
                      ->orWhere('status', 'Accepted');
                })
                ->where('is_assignment', true);
            });
        } else {
            $query->whereHas('movements', function($q) use ($status) {
                $q->where('status', $status)
                  ->where('is_assignment', true);
            });
        }
    }

    if ($fromDate && $fromDate != '') {
        $query->whereDate('created_at', '>=', $fromDate);
    }

    if ($toDate && $toDate != '') {
        $query->whereDate('created_at', '<=', $toDate);
    }

    $tapals = $query->orderBy('created_at', 'desc')->paginate(25);

    return view('tapals.tracing', compact('tapals', 'stats', 'users'),['pageConfigs'=> $pageConfigs]);
}

  protected function getTracingStatistics(Request $request)
{
    // Base query for all tapals (can be filtered by date range)
    $baseQuery = Tapal::query();

    if ($request->has('from_date') && $request->input('from_date') != '') {
        $baseQuery->whereDate('created_at', '>=', $request->input('from_date'));
    }

    if ($request->has('to_date') && $request->input('to_date') != '') {
        $baseQuery->whereDate('created_at', '<=', $request->input('to_date'));
    }

    // Total counts
    $totalTapals = $baseQuery->count();
    $totalPending = $this->getStatusCount($baseQuery, 'Pending');
    $totalCompleted = $this->getStatusCount($baseQuery, 'Completed');
    $totalAccepted = $this->getStatusCount($baseQuery, 'Accepted');
    $totalInProgress = $this->getStatusCount($baseQuery, 'In Progress');
    $totalOverdue = $this->getOverdueCountTracing($baseQuery);

    // Percentage calculations
    $pendingPercentage = $totalTapals > 0 ? round(($totalPending / $totalTapals) * 100, 2) : 0;
    $completedPercentage = $totalTapals > 0 ? round(($totalCompleted / $totalTapals) * 100, 2) : 0;
    $acceptedPercentage = $totalTapals > 0 ? round(($totalAccepted / $totalTapals) * 100, 2) : 0;
    $overduePercentage = $totalPending > 0 ? round(($totalOverdue / $totalPending) * 100, 2) : 0;

    return [
        'total_tapals' => $totalTapals,
        'total_pending' => $totalPending,
        'total_completed' => $totalCompleted,
        'total_accepted' => $totalAccepted,
        'total_in_progress' => $totalInProgress,
        'total_overdue' => $totalOverdue,
        'pending_percentage' => $pendingPercentage,
        'completed_percentage' => $completedPercentage,
        'accepted_percentage' => $acceptedPercentage,
        'overdue_percentage' => $overduePercentage
    ];
}

protected function getStatusCount($query, $status)
{
    $clone = clone $query;
    return $clone->whereHas('movements', function($q) use ($status) {
        $q->where('status', $status)
          ->where('is_assignment', true);
    })->count();
}

protected function getOverdueCountTracing($query)
{
    $clone = clone $query;
    return $clone->whereHas('movements', function($q) {
        $q->where(function($q) {
            $q->where('status', 'Pending')
              ->orWhere('status', 'Accepted');
        })
        ->where('is_assignment', true);
    })->count();
}



    protected function getOverdueCountTracing1($query)
    {
        $clone = clone $query;
        return $clone->whereHas('movements', function($q) {
            // $q->where('deadline', '<', now())
              $q->where(function($q) {
                  $q->where('status', 'like', '%pending%')
                    ->orWhere('status', 'like', '%in progress%');
              });
        })->count();
    }

    public function tracingShow(Tapal $tapal)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $tapal->load(['creator', 'currentHolder', 'movements.fromUser', 'movements.toUser', 'attachments']);
        return view('tapals.tracing_show', compact('tapal'),['pageConfigs'=> $pageConfigs]);
    }

     protected function getTapalStatistics($user)
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        // Base query for user's tapals
        $userTapalsQuery = Tapal::where(function($query) use ($user) {
            $query->where('current_holder_id', $user->id)
                ->orWhereHas('movements', function($q) use ($user) {
                    $q->where('to_user_id', $user->id);
                });
        });

        // Counts for different time periods
        $stats = [
            'today_count' => $userTapalsQuery->whereDate('created_at', $today)->count(),
            'today_completed' => $this->getCompletedCount($user, $today),

            'month_count' => $userTapalsQuery->where('created_at', '>=', $monthStart)->count(),
            'month_completed' => $this->getCompletedCount($user, $monthStart),

            'year_count' => $userTapalsQuery->where('created_at', '>=', $yearStart)->count(),
            'year_completed' => $this->getCompletedCount($user, $yearStart),

            'pending_count' => $this->getPendingCount($user),
            'completed_count' => $this->getCompletedCount($user),
            'overdue_count' => $this->getOverdueCount($user),

            'monthly_labels' => $this->getMonthlyLabels(),
            'monthly_data' => $this->getMonthlyData($user),
        ];

        return $stats;
    }

    protected function getPendingCount($user)
    {
        return Tapal::whereHas('movements', function($query) use ($user) {
            $query->where('to_user_id', $user->id)
                ->where(function($q) {
                    $q->where('status', 'like', '%Pending%')
                      ->orWhere('status', 'like', '%Accepted%');
                });
        })->count();
    }

    protected function getCompletedCount($user, $since = null)
    {
        $query = Tapal::whereHas('movements', function($query) use ($user) {
            $query->where('to_user_id', $user->id)
                ->where('status', 'like', '%Completed%');
        });

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query->count();
    }

    protected function getOverdueCount($user)
    {
        return Tapal::whereHas('movements', function($query) use ($user) {
            $query->where('to_user_id', $user->id)
                // ->where('deadline', '<', now())
                ->where(function($q) {
                    $q->where('status', 'like', '%Pending%')
                      ->orWhere('status', 'like', '%Accepted%');
                });
        })->count();
    }

    protected function getMonthlyLabels()
    {
        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->format('M Y');
        }
        return $labels;
    }

    protected function getMonthlyData($user)
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Tapal::where(function($query) use ($user) {
                    $query->where('current_holder_id', $user->id)
                        ->orWhereHas('movements', function($q) use ($user) {
                            $q->where('to_user_id', $user->id);
                        });
                })
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $data[] = $count;
        }
        return $data;
    }
}