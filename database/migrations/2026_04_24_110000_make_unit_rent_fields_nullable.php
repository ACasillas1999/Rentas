<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->decimal('monthly_rent', 12, 2)->nullable()->default(null)->change();
            $table->decimal('maintenance_amount', 12, 2)->nullable()->default(null)->change();
            $table->string('currency', 3)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->decimal('monthly_rent', 12, 2)->nullable(false)->default(0)->change();
            $table->decimal('maintenance_amount', 12, 2)->nullable(false)->default(0)->change();
            $table->string('currency', 3)->nullable(false)->default('MXN')->change();
        });
    }
};
