<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repara las bases de datos que corrieron la version original de
     * `create_return_document_details_table`, que no tenia la columna
     * `return_document_id` y ademas apuntaba `delivery_document_detail_id`
     * a la tabla `delivery_documents`. La migracion de creacion se edito
     * despues, por lo que esas bases nunca recibieron los cambios.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('return_document_details', 'return_document_id')) {
            Schema::table('return_document_details', function (Blueprint $table) {
                $table->foreignId('return_document_id')
                    ->constrained('return_documents')
                    ->cascadeOnDelete();
            });
        }

        $this->fixDeliveryDocumentDetailForeignKey();
    }

    public function down(): void
    {
        // No se revierte: la columna es parte del esquema esperado.
    }

    /**
     * Reapunta la llave foranea de `delivery_document_detail_id` a
     * `delivery_document_details` si quedo referenciando `delivery_documents`.
     */
    private function fixDeliveryDocumentDetailForeignKey(): void
    {
        foreach (Schema::getForeignKeys('return_document_details') as $foreignKey) {
            if ($foreignKey['columns'] !== ['delivery_document_detail_id']) {
                continue;
            }

            if ($foreignKey['foreign_table'] === 'delivery_document_details') {
                continue;
            }

            Schema::table('return_document_details', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey['name']);
                $table->foreign('delivery_document_detail_id')
                    ->references('id')
                    ->on('delivery_document_details')
                    ->cascadeOnDelete();
            });
        }
    }
};
