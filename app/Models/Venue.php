<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'venue_type_id', 'seating_capacity',
        'amenities', 'status', 'is_active'
    ];

    public function venueType()
    {
        return $this->belongsTo(VenueType::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('custom_amenities')
            ->withTimestamps();
    }
}
