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
        Schema::create('delivery_document_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_document_id')->constrained()->on('delivery_documents')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->on('equipments');
            $table->string('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_document_details');
    }
};
