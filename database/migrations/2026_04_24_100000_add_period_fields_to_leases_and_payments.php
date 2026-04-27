<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar tabla leases: quitar due_day / maintenance_due_day, agregar first_period_start
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['due_day', 'maintenance_due_day']);
            $table->date('first_period_start')->nullable()->after('end_date');
        });

        // Modificar tabla payments: agregar campos de periodo
        Schema::table('payments', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('period_label');
            $table->date('period_end')->nullable()->after('period_start');
            $table->unsignedSmallInteger('period_number')->nullable()->after('period_end');
            $table->unsignedSmallInteger('total_periods')->nullable()->after('period_number');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('first_period_start');
            $table->unsignedTinyInteger('due_day')->default(5);
            $table->unsignedTinyInteger('maintenance_due_day')->default(5);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end', 'period_number', 'total_periods']);
        });
    }
};
