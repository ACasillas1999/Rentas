<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 120)->nullable();   // Guardado en el momento
            $table->string('user_role', 30)->nullable();    // Rol al momento de la acción
            $table->string('action', 40);                  // created, updated, deleted, paid, invoiced, viewed, exported, login
            $table->string('module', 40);                  // lease, payment, tenant, property, unit, expense, user, report, auth
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');                   // Mensaje legible para humanos
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module', 'action']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
