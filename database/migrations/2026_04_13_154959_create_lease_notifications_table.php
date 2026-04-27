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
        Schema::create('lease_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->boolean('notify_30_days')->default(true);
            $table->boolean('notify_15_days')->default(true);
            $table->boolean('notify_end_date')->default(true);
            
            // Trackers para evitar duplicados
            $table->timestamp('sent_30_days_at')->nullable();
            $table->timestamp('sent_15_days_at')->nullable();
            $table->timestamp('sent_end_date_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_notifications');
    }
};
