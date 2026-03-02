<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'event_type_id', 'event_mode_id', 'coordinator_id',
        'faculty_id', 'participants_count', 'event_category',
        'external_entity', 'venue_type_id', 'venue_id', 'external_venue','user_id','status','cancelled_at','cancelled_by'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function eventMode()
    {
        return $this->belongsTo(EventMode::class);
    }

    // public function coordinator()
    // {
    //     return $this->belongsTo(User::class, 'coordinator_id');
    // }

    // public function faculty()
    // {
    //     return $this->belongsTo(User::class, 'faculty_id');
    // }
    public function creator()
{
    return $this->belongsTo(User::class, 'user_id');
}

    public function venueType()
    {
        return $this->belongsTo(VenueType::class);
    }

    // public function venue()
    // {
    //     return $this->belongsTo(Venue::class);
    // }

   public function venues()
{
    return $this->belongsToMany(Venue::class)
        ->withPivot('custom_amenities')
        ->withTimestamps();
}

    public function coordinators()
    {
        return $this->belongsToMany(User::class, 'event_coordinator');
    }

    public function faculties()
    {
        return $this->belongsToMany(User::class, 'event_faculty');
    }

}
