<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $col) {
            $col->text('receipt')->nullable()->change();
            $col->text('invoice_pdf')->nullable()->change();
            $col->text('invoice_xml')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $col) {
            $col->string('receipt', 255)->nullable()->change();
            $col->string('invoice_pdf', 255)->nullable()->change();
            $col->string('invoice_xml', 255)->nullable()->change();
        });
    }
};
