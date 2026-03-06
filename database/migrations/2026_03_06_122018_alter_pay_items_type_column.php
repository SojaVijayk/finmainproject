<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old enum column and re-add as string to support Deduction/Allowance/Recovery
        DB::statement("ALTER TABLE pay_items MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'Deduction'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pay_items MODIFY COLUMN `type` ENUM('Professional Tax','Festival Allowance','Bonus Allowance') NOT NULL");
    }
};
