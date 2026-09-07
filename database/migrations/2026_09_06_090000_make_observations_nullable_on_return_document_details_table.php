<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las observaciones de un equipo devuelto son opcionales, igual que en
     * `delivery_document_details`.
     */
    public function up(): void
    {
        Schema::table('return_document_details', function (Blueprint $table) {
            $table->string('observations')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('return_document_details', function (Blueprint $table) {
            $table->string('observations')->nullable(false)->change();
        });
    }
};
