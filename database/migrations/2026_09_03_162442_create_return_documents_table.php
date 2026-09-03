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
        Schema::create('return_documents', function (Blueprint $table) {
            $table->id();
            $table->datetime('return_date');
            $table->string('responsable_signature');
            $table->string('administrador_signature');
            $table->string('observations');
            $table->foreignId('delivery_document_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_documents');
    }
};
