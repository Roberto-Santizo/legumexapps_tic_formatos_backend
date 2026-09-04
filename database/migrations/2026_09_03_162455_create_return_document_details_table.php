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
        Schema::create('return_document_details', function (Blueprint $table) {
            $table->id();
            $table->string('observations');
            $table->foreignId('return_document_id')->constrained()->on('return_documents')->cascadeOnDelete();;
            $table->foreignId('delivery_document_detail_id')->constrained()->on('delivery_document_details')->cascadeOnDelete();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_document_details');
    }
};
