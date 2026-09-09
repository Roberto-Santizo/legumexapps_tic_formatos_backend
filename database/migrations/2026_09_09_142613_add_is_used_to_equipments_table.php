<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade `is_used` a las bases de datos que se migraron mientras la columna
     * estaba comentada en la migración de creación.
     */
    public function up(): void
    {
        if (Schema::hasColumn('equipments', 'is_used')) {
            return;
        }

        Schema::table('equipments', function (Blueprint $table) {
            $table->boolean('is_used')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropColumn('is_used');
        });
    }
};
