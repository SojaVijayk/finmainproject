<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventMode;
use App\Models\VenueType;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventConfirmation;
use App\Mail\EventCancellation;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        return view('bookings.calendar',['pageConfigs'=> $pageConfigs]);
    }
    public function Avialability()
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        return view('bookings.calendar_availability',['pageConfigs'=> $pageConfigs]);
    }

    public function getEvents()
{
 $colorMap = [
    'Workshop' => '#007bff',
    'Seminar' => '#28a745',
    'Meeting' => '#ffc107',
    'Training' => '#17a2b8',
    'Webinar' => '#6f42c1',
    'Conference' => '#fd7e14',
    'Recruitment' => '#dc3545',
    'Discussion' => '#6610f2',
    'Default' => '#20c997'
];
    $events = Event::with([
            'eventType',
            'eventMode',
            'venueType',
            'venues',
            'coordinators',
            'faculties',
            'creator'
        ])
        ->get()
        ->map(function ($event) use ($colorMap) {
            $canEdit = auth()->user()->id === $event->user_id ||
                      $event->coordinators->contains(auth()->id()) ||
                      ($event->faculties && $event->faculties->contains(auth()->id()));
            $eventType = $event->eventType->name ?? 'Default';
            // $color = $canEdit ? ($colorMap[$eventType] ?? $colorMap['Default']) : '#6c757d';
            $color =  ($colorMap[$eventType] ?? $colorMap['Default']) ;


            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'eventType' => $event->eventType->name,
                'eventMode' => $event->eventMode->name,
                'coordinators' => $event->coordinators->pluck('name'),
                'faculties' => $event->faculties->pluck('name'),
                'participants_count' => $event->participants_count,
                'event_category' => $event->event_category,
                'external_entity' => $event->external_entity,
                'venue_type' => $event->venueType->name,
                'venues' => $event->venues->pluck('name'),
                'custom_amenities_request' => $event->custom_amenities_request,
                'creator' => $event->creator->name,
                'created_at' => $event->created_at->format('M j, Y g:i A'),
                'canEdit' => $canEdit,
                // 'color' => $canEdit ? null : '#6c757d'
                'color' => $color,
                'status' =>$event->status
            ];
        });

    return response()->json($events);
}

    public function store(Request $request)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'event_type_id' => 'required|exists:event_types,id',
        'event_mode_id' => 'required|exists:event_modes,id',
        'coordinators' => 'nullable|array',
        'coordinators.*' => 'exists:users,id',
        'faculties' => 'nullable|array',
        'faculties.*' => 'exists:users,id',
        'participants_count' => 'nullable|integer|min:0',
        'event_category' => 'required|in:CMD,External',
        'external_entity' => 'nullable|required_if:event_category,External|string|max:255',
        'venue_type_id' => 'required|exists:venue_types,id',
        'venues' => 'required_if:venue_type_id,1,3|array',
        'venues.*' => 'exists:venues,id',
        'custom_amenities_request' => 'nullable|string',
        'external_venue' => 'nullable|required_if:venue_type_id,2|string|max:255',
    ]);

         // Check venue availability
    if ($request->venues) {
        $conflicts = $this->checkVenueAvailability($request->venues, $request->start_date, $request->end_date);

        if (!empty($conflicts)) {
            // return response()->json([
            //     'error' => 'The following venues are already booked during this time: ' . implode(', ', $conflicts)
            // ], 422);
            return response()->json([
    'message' => 'The following venues are already booked during this time.',
    'errors' => [
        'venues' => ['The following venues are already booked: ' . implode(', ', $conflicts)]
    ]
], 422);
        }
    }


 // Create event
    $eventData = $request->except(['coordinators', 'faculties', 'venues']);
    $eventData['user_id'] = auth()->id();
     $eventData['status'] = 1;
    $event = Event::create($eventData);

    // Attach coordinators
    if ($request->coordinators) {
    $event->coordinators()->sync($request->coordinators);
    }

    // Attach faculties if provided
    if ($request->faculties) {
        $event->faculties()->sync($request->faculties);
    }

     // Attach venues with custom amenities
    if ($request->venues) {
        $venuesWithAmenities = [];
        foreach ($request->venues as $venueId) {
            $venuesWithAmenities[$venueId] = ['custom_amenities' => $request->custom_amenities_request];
        }
        $event->venues()->sync($venuesWithAmenities);
    }

// Send confirmation emails to all coordinators
if ($request->coordinators) {
    foreach ($event->coordinators as $coordinator) {
        // Mail::to($coordinator->email)->cc('admin@cmd.kerala.gov.in')->send(new EventConfirmation($event));
        // Mail::to($coordinator->email)->send(new EventConfirmation($event));
    }
  }
  if ($request->faculties) {
    foreach ($event->faculties as $facultie) {
        // Mail::to($facultie->email)->send(new EventConfirmation($event));
    }
  }

        return response()->json($event);
    }

    // Add new method to get available venues
public function getAvailableVenuesForTimeOLD(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ]);

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $excludeEventId = $request->input('exclude_event');

    // Get all active venues
    $allVenues = Venue::where('status', 'active')
        ->where('is_active', true)
        ->get();

    // Get venues that are already booked during this time (excluding current event if editing)
    $query = Event::where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($query) use ($startDate, $endDate) {
                      $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                  });
        })
        ->whereHas('venues');

    if ($excludeEventId) {
        $query->where('id', '!=', $excludeEventId);
    }

    $bookedVenues = $query->get()
        ->flatMap(function ($event) {
            return $event->venues->pluck('id');
        })
        ->unique()
        ->toArray();

    // Filter available venues
    $availableVenues = $allVenues->reject(function ($venue) use ($bookedVenues) {
        return in_array($venue->id, $bookedVenues);
    });

    return response()->json($availableVenues->values());
}

public function getAvailableVenuesForTime(Request $request)
{
    // $validator = Validator::make($request->all(), [
    //     'start_date' => 'required|date',
    //     'end_date' => 'required|date|after:start_date',
    //     'exclude_event' => 'nullable|exists:events,id'
    // ]);
     $request->validate([
       'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'exclude_event' => 'nullable|exists:events,id'
    ]);



    try {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $excludeEventId = $request->input('exclude_event');

        // Get all active venues
        $allVenues = Venue::where('status', 'active')
            ->where('is_active', true)
            ->get();

        // Get conflicting events
        $conflictingEvents = Event::where('status',1)->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($query) use ($startDate, $endDate) {
                          $query->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                      });
            })
            ->whereHas('venues');

        if ($excludeEventId) {
            $conflictingEvents->where('id', '!=', $excludeEventId);
        }

        $bookedVenueIds = $conflictingEvents->get()
            ->flatMap(function ($event) {
                return $event->venues->pluck('id');
            })
            ->unique()
            ->values()
            ->toArray();

        // Filter available venues
        $availableVenues = $allVenues->reject(function ($venue) use ($bookedVenueIds) {
            return in_array($venue->id, $bookedVenueIds);
        });

        return response()->json($availableVenues->values());

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error checking venue availability',
            'error' => $e->getMessage()
        ], 500);
    }
}

        // Add these methods to check authorization
    private function canEditEvent(Event $event)
    {
        // return auth()->user()->id === $event->coordinator_id ||
        //       auth()->user()->id === $event->faculty_id ||
        //       auth()->user()->id === 30 || //for administrative officer
        //       auth()->user()->id === $event->user_id; // Assuming you have user_id as creator

         // Check if user is admin (ID 30 is administrative officer)
    if (auth()->user()->id === 30) {
        return true;
    }

    // Check time window first (if defined)
    // $now = now();
    // if ($event->start_date && $event->end_date) {
    //     if (!$now->between($event->start_date, $event->end_date)) {
    //         return false;
    //     }
    // }

    // Check if user is the event creator
    if (auth()->user()->id === $event->user_id) {
        return true;
    }

    // Check if user is one of the coordinators
    if ($event->coordinators->contains('id', auth()->id())) {
        return true;
    }

    // Check if user is one of the faculties
    if ($event->faculties->contains('id', auth()->id())) {
        return true;
    }

    return false;
    }
   public function show(Event $event)
{
    $event->load([
        'eventType',
        'eventMode',
        'venueType',
        'venues',
        'coordinators' => function($query) {
            $query->select('users.id', 'users.name');
        },
        'faculties' => function($query) {
            $query->select('users.id', 'users.name');
        },
        'creator'
    ]);

    return response()->json([
        'id' => $event->id,
        'title' => $event->title,
        'description' => $event->description,
        'start_date' => $event->start_date,
        'end_date' => $event->end_date,
        'event_type_id' => $event->event_type_id,
        'event_type' => $event->eventType->name,
        'event_mode_id' => $event->event_mode_id,
        'event_mode' => $event->eventMode->name,
        'coordinators' => $event->coordinators,
        'faculties' => $event->faculties,
        'participants_count' => $event->participants_count,
        'event_category' => $event->event_category,
        'external_entity' => $event->external_entity,
        'venue_type_id' => $event->venue_type_id,
        'venue_type' => $event->venueType->name,
        'venues' => $event->venues->map(function($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'custom_amenities' => $venue->pivot->custom_amenities
            ];
        }),
        'custom_amenities_request' => $event->custom_amenities_request,
        'external_venue' => $event->external_venue,
        'creator' => $event->creator->only(['id', 'name']),
        'created_at' => $event->created_at->format('M j, Y g:i A'),
        'can_edit' => $this->canEditEvent($event),
        'status' => $event->status
    ]);
}

    public function update(Request $request, Event $event)
{
    if (!$this->canEditEvent($event)) {
        return response()->json(['error' => 'Unauthorized to edit this event'], 403);
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'event_type_id' => 'required|exists:event_types,id',
        'event_mode_id' => 'required|exists:event_modes,id',
        'coordinators' => 'nullable|array',
        'coordinators.*' => 'exists:users,id',
        'faculties' => 'nullable|array',
        'faculties.*' => 'exists:users,id',
        'participants_count' => 'nullable|integer|min:0',
        'event_category' => 'required|in:CMD,External',
        'external_entity' => 'nullable|required_if:event_category,External|string|max:255',
        'venue_type_id' => 'required|exists:venue_types,id',
        'venues' => 'required_if:venue_type_id,1,3|array',
        'venues.*' => 'exists:venues,id',
        'custom_amenities_request' => 'nullable|string',
        'external_venue' => 'nullable|required_if:venue_type_id,2|string|max:255',
    ]);

    // Check venue availability (excluding current event)
    if ($request->venues) {
        $conflicts = $this->checkVenueAvailability($request->venues, $request->start_date, $request->end_date, $event->id);

        if (!empty($conflicts)) {
            return response()->json([
                'error' => 'The following venues are already booked during this time: ' . implode(', ', $conflicts)
            ], 422);
        }
    }

    // Update basic event data
    $event->update($request->except(['coordinators', 'faculties', 'venues']));

    // Sync coordinators
    $event->coordinators()->sync($request->coordinators);

    // Sync faculties if provided
    $event->faculties()->sync($request->faculties ?? []);

    // Sync venues with custom amenities
    if ($request->venues) {
        $venuesWithAmenities = [];
        foreach ($request->venues as $venueId) {
            $venuesWithAmenities[$venueId] = ['custom_amenities' => $request->custom_amenities_request];
        }
        $event->venues()->sync($venuesWithAmenities);
    } else {
        $event->venues()->detach();
    }

    // Send confirmation emails to all coordinators
    // foreach ($event->coordinators as $coordinator) {
    //     Mail::to($coordinator->email)->send(new EventConfirmation($event));
    // }

    // Send confirmation emails to all coordinators
if ($request->coordinators) {
    foreach ($event->coordinators as $coordinator) {
        Mail::to($coordinator->email)->cc('admin@cmd.kerala.gov.in')->send(new EventConfirmation($event));
        // Mail::to($coordinator->email)->send(new EventConfirmation($event));
    }
  }
  if ($request->faculties) {
    foreach ($event->faculties as $facultie) {
        Mail::to($facultie->email)->send(new EventConfirmation($event));
    }
  }


    return response()->json($event->load(['eventType', 'eventMode', 'venues', 'coordinators', 'faculties']));
}

    public function destroy(Event $event)
    {
       if (!$this->canEditEvent($event)) {
        return response()->json(['error' => 'Unauthorized to delete this event'], 403);
      }
        $event->delete();
        return response()->json(null, 204);
    }

    public function getFormData()
    {
        $eventTypes = EventType::where('is_active', true)->get();
        $eventModes = EventMode::where('is_active', true)->get();
        $venueTypes = VenueType::where('is_active', true)->get();
        $venues = Venue::where('is_active', true)->where('status', 'active')->get();
        $users = User::where('active', 1)->orderBy('name')->get();
        $faculties = User::select('users.*')->where('active',1)->join("employees","employees.user_id","=","users.id")->whereIn('employees.designation',[2,7,9])->get();

        return response()->json([
            'eventTypes' => $eventTypes,
            'eventModes' => $eventModes,
            'venueTypes' => $venueTypes,
            'venues' => $venues,
            'users' => $users,
            'faculties' => $faculties,
        ]);
    }

    private function checkVenueAvailability($venueIds, $startDate, $endDate, $excludeEventId = null)
{
    $conflicts = [];

    foreach ($venueIds as $venueId) {
        $query = Event::whereHas('venues', function($q) use ($venueId) {
                $q->where('venue_id', $venueId);
            })
            ->where('status',1)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($query) use ($startDate, $endDate) {
                          $query->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                      });
            });

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        if ($query->exists()) {
            $venue = Venue::find($venueId);
            $conflicts[] = $venue->name;
        }
    }

    return $conflicts;
}

private function getAvailableVenues($startDate, $endDate)
{
    $bookedVenueIds = Event::where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($query) use ($startDate, $endDate) {
                      $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                  });
        })
        ->whereHas('venues')
        ->pluck('id');

    return Venue::where('status', 'active')
        ->where('is_active', true)
        ->whereNotIn('id', function($query) use ($startDate, $endDate) {
            $query->select('venue_id')
                ->from('event_venue')
                ->join('events', 'events.id', '=', 'event_venue.event_id')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function($query) use ($startDate, $endDate) {
                              $query->where('start_date', '<', $startDate)
                                    ->where('end_date', '>', $endDate);
                          });
                });
        })
        ->get();
}


// public function getHallAvailability(Request $request)
// {
//     $filter = $request->input('filter', 'now');
//     $date = $request->input('date');

//     $now = Carbon::now();

//     switch ($filter) {
//         case 'today':
//             $start = $now->copy()->startOfDay();
//             $end = $now->copy()->endOfDay();
//             break;
//         case 'custom':
//             if (!$date) {
//                 return response()->json(['error' => 'Date parameter required'], 400);
//             }
//             $start = Carbon::parse($date)->startOfDay();
//             $end = Carbon::parse($date)->endOfDay();
//             break;
//         case 'now':
//         default:
//             $start = $now;
//             $end = $now->copy()->addHours(2);
//             break;
//     }

//     // Get all active venues
//     $allVenues = Venue::where('status', 'active')
//         ->where('is_active', true)
//         ->get();

//     // Get booked venues and their events during this time
//     $bookedEvents = Event::where(function($query) use ($start, $end) {
//             $query->whereBetween('start_date', [$start, $end])
//                   ->orWhereBetween('end_date', [$start, $end])
//                   ->orWhere(function($query) use ($start, $end) {
//                       $query->where('start_date', '<', $start)
//                             ->where('end_date', '>', $end);
//                   });
//         })
//         ->whereHas('venues')
//         ->with(['venues'])
//         ->get();

//     // Get booked venue IDs
//     $bookedVenueIds = $bookedEvents->flatMap(function ($event) {
//         return $event->venues->pluck('id');
//     })->unique()->toArray();

//     // Available venues (not booked)
//     $availableVenues = $allVenues->reject(function ($venue) use ($bookedVenueIds) {
//         return in_array($venue->id, $bookedVenueIds);
//     });

//     // Format booked events for display
//     $bookedVenues = [];
//     foreach ($bookedEvents as $event) {
//         foreach ($event->venues as $venue) {
//             $bookedVenues[] = [
//                 'title' => $event->title,
//                 'start_date' => $event->start_date,
//                 'end_date' => $event->end_date,
//                 'venue_name' => $venue->name
//             ];
//         }
//     }

//     return response()->json([
//         'availableVenues' => $availableVenues,
//         'bookedVenues' => $bookedVenues
//     ]);
// }

public function getHallAvailability(Request $request)
{
    $filter = $request->input('filter', 'now');
    $date = $request->input('date');

    $now = Carbon::now();

    switch ($filter) {
        case 'today':
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            break;
        case 'custom':
            if (!$date) {
                return response()->json(['error' => 'Date parameter required'], 400);
            }
            $start = Carbon::parse($date)->startOfDay();
            $end = Carbon::parse($date)->endOfDay();
            break;
        case 'now':
        default:
            $start = $now;
            $end = $now->copy()->addHours(2);
            break;
    }

    // Get all active venues
    $allVenues = Venue::where('status', 'active')
        ->where('is_active', true)
        ->get();

    // Get booked venues and their events during this time
    $bookedEvents = Event::where('status',1)->where(function($query) use ($start, $end) {
            $query->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($query) use ($start, $end) {
                      $query->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                  });
        })
        ->whereHas('venues')
        ->with(['venues'])
        ->get();

    // Get booked venue IDs
    $bookedVenueIds = $bookedEvents->flatMap(function ($event) {
        return $event->venues->pluck('id');
    })->unique()->toArray();

    // Format available venues as array (not keyed by ID)
    $availableVenues = $allVenues->reject(function ($venue) use ($bookedVenueIds) {
        return in_array($venue->id, $bookedVenueIds);
    })->values(); // Use values() to reset keys to sequential numbers

    // Format booked events for display
    $bookedVenues = [];
    foreach ($bookedEvents as $event) {
        foreach ($event->venues as $venue) {
            $bookedVenues[] = [
                'title' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'venue_name' => $venue->name
            ];
        }
    }

    return response()->json([
        'availableVenues' => $availableVenues,
        'bookedVenues' => $bookedVenues
    ]);
}

// public function getMyEvents(Request $request)
// {
//     $filter = $request->input('filter', 'upcoming');
//     $date = $request->input('date');
//     $user = auth()->user();

//     $query = Event::where('user_id', $user->id)
//         ->orWhereHas('coordinators', function($q) use ($user) {
//             $q->where('user_id', $user->id);
//         })
//         ->orWhereHas('faculties', function($q) use ($user) {
//             $q->where('user_id', $user->id);
//         })
//         ->with(['eventType', 'venues', 'coordinators']);

//     switch ($filter) {
//         case 'today':
//             $query->whereDate('start_date', Carbon::today());
//             break;
//         case 'custom':
//             if (!$date) {
//                 return response()->json(['error' => 'Date parameter required'], 400);
//             }
//             $query->whereDate('start_dates', Carbon::parse($date));
//             break;
//         case 'upcoming':
//         default:
//             $query->where('start_date', '>=', now());
//             break;
//     }

//     $events = $query->orderBy('start_date')
//         ->get()
//         ->map(function ($event) {
//             return [
//                 'id' => $event->id,
//                 'title' => $event->title,
//                 'start_date' => $event->start_date,
//                 'end_date' => $event->end_date,
//                 'event_type' => $event->eventType->name,
//                 'venue_names' => $event->venues->pluck('name'),
//                 'coordinator_names' => $event->coordinators->pluck('name'),
//                 'color' => $this->getEventColor($event->eventType->name)
//             ];
//         });

//     return response()->json($events);
// }
public function getMyEvents(Request $request)
{
    $filter = $request->input('filter', 'upcoming');
    $date = $request->input('date');
    $user = auth()->user();

    $query = Event::where('user_id', $user->id)
        ->orWhereHas('coordinators', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->orWhereHas('faculties', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['eventType', 'venues', 'coordinators']);

    switch ($filter) {
        case 'today':
            $query->whereDate('start_date', Carbon::today())
                ->orWhereDate('end_date', Carbon::today())
                ->orWhere(function($q) {
                    $q->where('start_date', '<', Carbon::today())
                      ->where('end_date', '>', Carbon::today());
                });
            break;
        case 'custom':
            if (!$date) {
                return response()->json(['error' => 'Date parameter required'], 400);
            }
            $customDate = Carbon::parse($date);
            $query->where(function($q) use ($customDate) {
                $q->whereDate('start_date', $customDate)
                  ->orWhereDate('end_date', $customDate)
                  ->orWhere(function($q) use ($customDate) {
                      $q->where('start_date', '<', $customDate)
                        ->where('end_date', '>', $customDate);
                  });
            });
            break;
        case 'upcoming':
        default:
            $query->where('end_date', '>=', now()); // Changed from start_date to end_date
            break;
    }

    $events = $query->orderBy('start_date')
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'event_type' => $event->eventType->name,
                'venue_names' => $event->venues->pluck('name'),
                'coordinator_names' => $event->coordinators->pluck('name'),
                'color' => $this->getEventColor($event->eventType->name)
            ];
        });

    return response()->json($events);
}

public function getUpcomingEvents(Request $request)
{
    $filter = $request->input('filter', 'upcoming');
    $date = $request->input('date');

    $query = Event::where('status',1)->with(['eventType', 'venues', 'coordinators']);

    switch ($filter) {
        case 'today':
            $query->whereDate('start_date', Carbon::today());
            break;
        case 'custom':
            if (!$date) {
                return response()->json(['error' => 'Date parameter required'], 400);
            }
            $query->whereDate('start_date', Carbon::parse($date));
            break;
        case 'upcoming':
        default:
            $query->where('start_date', '>=', now());
            break;
    }

    $events = $query->orderBy('start_date')
        ->limit(20)
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'event_type' => $event->eventType->name,
                'venue_names' => $event->venues->pluck('name'),
                'coordinator_names' => $event->coordinators->pluck('name'),
                'color' => $this->getEventColor($event->eventType->name)
            ];
        });

    return response()->json($events);
}

private function getEventColor($eventType)
{
    $colorMap = [
        'Workshop' => '#007bff',
        'Seminar' => '#28a745',
        'Meeting' => '#ffc107',
        'Training' => '#17a2b8',
        'Webinar' => '#6f42c1',
        'Conference' => '#fd7e14',
        'Recruitment' => '#dc3545',
        'Discussion' => '#6610f2',
        'Default' => '#20c997'
    ];

    return $colorMap[$eventType] ?? $colorMap['Default'];
}


public function cancel(Request $request, Event $event)
{
    // Check if user has permission to cancel
    if (!$this->canEditEvent($event)) {
        return response()->json(['error' => 'Unauthorized to edit this event'], 403);
    }

    // Soft delete the event (or update status if you prefer)
    $event->update(['status' => 2,'status' => 2,'cancelled_by'=>auth()->user()->id,'cancelled_at'=>now()]);
    // Or use $event->delete() for soft delete

    // Send cancellation notifications
    $this->sendCancellationNotifications($event);

    return response()->json(['message' => 'Event cancelled successfully']);
}

private function sendCancellationNotifications(Event $event)
{
    $recipients = $event->coordinators->merge($event->faculties);

    foreach ($recipients as $user) {
        Mail::to($user->email)->send(new EventCancellation($event));
    }
}



public function getVenues()
{
    $venues = Venue::where('status', 'active')
                   ->where('is_active', true)
                   ->get(['id', 'name']);

    return response()->json($venues);
}

// public function getBookings(Request $request)
// {
//     $startInput = $request->query('start');
//     $endInput   = $request->query('end');

//     // Fix timezone formatting issue: replace " 05:30" with "+05:30"
//     $startInput = preg_replace('/\s(\d{2}:\d{2})$/', '+$1', $startInput);
//     $endInput   = preg_replace('/\s(\d{2}:\d{2})$/', '+$1', $endInput);

//     $start = Carbon::parse($startInput);
//     $end   = Carbon::parse($endInput);

//     $bookings = Event::where('status', 1)
//         ->where(function($query) use ($start, $end) {
//             $query->whereBetween('start_date', [$start, $end])
//                   ->orWhereBetween('end_date', [$start, $end])
//                   ->orWhere(function($query) use ($start, $end) {
//                       $query->where('start_date', '<', $start)
//                             ->where('end_date', '>', $end);
//                   });
//         })
//         ->with('venues:id,name')
//         ->get()
//         ->map(function($event) {
//             return [
//                 'title' => $event->title,
//                 'start_date' => $event->start_date,
//                 'end_date' => $event->end_date,
//                 'venue_id' => $event->venues->first()->id ?? null,
//                 'venue_name' => $event->venues->first()->name ?? null,
//             ];
//         });

//     return response()->json($bookings);
// }
public function getBookings(Request $request)
{
        $startInput = $request->query('start');
    $endInput   = $request->query('end');

    // Fix timezone formatting issue: replace " 05:30" with "+05:30"
    $startInput = preg_replace('/\s(\d{2}:\d{2})$/', '+$1', $startInput);
    $endInput   = preg_replace('/\s(\d{2}:\d{2})$/', '+$1', $endInput);

    $start = Carbon::parse($startInput);
    $end   = Carbon::parse($endInput);
try {
        $start = Carbon::parse($startInput)->startOfDay();
        $end   = Carbon::parse($endInput)->endOfDay();
    } catch (\Throwable $e) {
        // Fallback: trim to date part only
        $start = Carbon::parse(substr($startInput, 0, 10))->startOfDay();
        $end   = Carbon::parse(substr($endInput,   0, 10))->endOfDay();
    }

    // Query events that overlap the visible range
    $events = Event::where('status', 1)
        ->where(function($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('end_date',   [$start, $end])
              ->orWhere(function($q) use ($start, $end) {
                  $q->where('start_date', '<', $start)
                    ->where('end_date',   '>', $end);
              });
        })
        ->with(['venues:id,name', 'creator:id,name'])
        ->get();

    // IMPORTANT: explode multi-venue events => one FC event per venue,
    // and make sure resourceId === venue.id (as string) to match resources.
    $bookings = $events->flatMap(function ($event) {
        return $event->venues->map(function ($venue) use ($event) {
            return [
                'id'         => 'E'.$event->id.'V'.$venue->id,              // unique per event+venue
                'resourceId' => (string) $venue->id,                         // MUST match resources list
                'title'      => $event->title,
                // 'start'      => $event->start_date->format('Y-m-d\TH:i:sP'), // ISO8601 w/ offset
                // 'end'        => $event->end_date->format('Y-m-d\TH:i:sP'),
                 'start'           => Carbon::parse($event->start_date)->format('Y-m-d\TH:i:sP'),
                    'end'             => Carbon::parse($event->end_date)->format('Y-m-d\TH:i:sP'),
                // Extra details for tooltip
                'extendedProps' => [
                    'venue_name'   => $venue->name,
                    'participants' => (int) ($event->participants_count ?? 0),
                    'booked_by'    => $event->creator->name ?? 'N/A',
                    // 'start_hm'     => $event->start_date->format('d M Y, H:i'),
                    // 'end_hm'       => $event->end_date->format('d M Y, H:i'),
                       'start_hm'           => Carbon::parse($event->start_date)->format('d M Y, H:i'),
                    'end_hm'             => Carbon::parse($event->end_date)->format('d M Y, H:i'),

                ],
            ];
        });
    })->values();

    return response()->json($bookings);
}


}
