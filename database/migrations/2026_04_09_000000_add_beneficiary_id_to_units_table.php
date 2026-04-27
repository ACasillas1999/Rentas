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
        Schema::table('units', function (Blueprint $chunk) {
            $chunk->foreignId('beneficiary_id')->nullable()->after('property_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $chunk) {
            $chunk->dropForeign(['beneficiary_id']);
            $chunk->dropColumn('beneficiary_id');
        });
    }
};
