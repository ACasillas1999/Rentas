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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('notes');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('notes');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('notes');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->string('contract_pdf')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('photo');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('photo');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('photo');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('contract_pdf');
        });
    }
};
