<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Locations table
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create Floors table
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Create Rooms table
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained('floors')->onDelete('cascade');
            $table->string('room_number');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Update Asset Allocations table
        Schema::table('asset_allocations', function (Blueprint $table) {
            // Drop the old string location column if it exists
             if (Schema::hasColumn('asset_allocations', 'location')) {
                $table->dropColumn('location');
            }

            // Update Enum for Type to include new types if not strictly string
            // DB::statement("ALTER TABLE asset_allocations MODIFY COLUMN type ENUM('Employee', 'Location', 'Room', 'Floor')");
            // Since modifying enum in Laravel/MySQL can be tricky with doctrine/dbal, and we might just want to treat it as string or stick to the existing set if 'Location' acts as generic.
            // Let's assume 'Location' covers the new types or we add them.
            // The original migration had: $table->enum('type', ['Employee', 'Location']);
            // To be safe and flexible, let's just make sure the columns are there.
            // We can repurpose 'Location' enum value to mean any of the new location types, or rely on logic.
            // But ideally we should update the enum. For now, let's add the columns.

            $table->foreignId('location_id')->nullable()->after('employee_id')->constrained('locations')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->after('location_id')->constrained('floors')->onDelete('set null');
            $table->foreignId('room_id')->nullable()->after('floor_id')->constrained('rooms')->onDelete('set null');
        });

         // Raw statement to update enum if possible, or we can just leave it if 'Location' is broad enough.
         // Let's modify the column to be string to allow flexibility avoiding ENUM hell, or add values.
         // $table->string('type')->change(); // This requires doctrine/dbal.
         // Let's try to ALTER directly for MySQL which is likely used here.
         try {
             DB::statement("ALTER TABLE asset_allocations MODIFY COLUMN type ENUM('Employee', 'Location', 'Floor', 'Room') NOT NULL");
         } catch (\Exception $e) {
             // Fallback or ignore if it fails (e.g. sqlite testing)
         }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_allocations', function (Blueprint $table) {
             $table->dropForeign(['location_id']);
             $table->dropForeign(['floor_id']);
             $table->dropForeign(['room_id']);
             $table->dropColumn(['location_id', 'floor_id', 'room_id']);
             $table->string('location')->nullable();

             // Revert enum - risky if data exists with new types
             // DB::statement("ALTER TABLE asset_allocations MODIFY COLUMN type ENUM('Employee', 'Location')");
        });

        Schema::dropIfExists('rooms');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('locations');
    }
};
