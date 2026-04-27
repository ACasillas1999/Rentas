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
        Schema::table('leases', function (Blueprint $table) {
            $table->tinyInteger('maintenance_due_day')->after('due_day')->nullable();
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE leases SET maintenance_due_day = due_day');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('maintenance_due_day');
        });
    }
};
