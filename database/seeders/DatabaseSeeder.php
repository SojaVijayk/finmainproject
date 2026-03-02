<?php

namespace Database\Seeders;
use App\Models\EventType;
use App\Models\EventMode;
use App\Models\VenueType;
use App\Models\Venue;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // $this->call(VenuesTableSeeder::class);
        // Event Types
        $eventTypes = ['Meeting', 'Training', 'Workshop', 'Seminar', 'Conference'];
        foreach ($eventTypes as $type) {
            EventType::create(['name' => $type]);
        }

        // Event Modes
        $eventModes = ['Online', 'Offline', 'Hybrid'];
        foreach ($eventModes as $mode) {
            EventMode::create(['name' => $mode]);
        }

        // Venue Types
        $venueTypes = ['Inhouse', 'External', 'Both'];
        foreach ($venueTypes as $type) {
            VenueType::create(['name' => $type]);
        }

        // Venues
        $venues = [
            ['name' => 'Conference Room A', 'venue_type_id' => 1, 'seating_capacity' => 20, 'amenities' => 'Projector, Whiteboard'],
            ['name' => 'Conference Room B', 'venue_type_id' => 1, 'seating_capacity' => 30, 'amenities' => 'Projector, Video Conferencing'],
            ['name' => 'Training Hall', 'venue_type_id' => 1, 'seating_capacity' => 50, 'amenities' => 'Projector, Sound System'],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}
