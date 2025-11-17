<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Renomear value para amount (se existir)
            if (Schema::hasColumn('opportunities', 'value')) {
                $table->renameColumn('value', 'amount');
            }
            
            // Adicionar expected_close_date se não existir
            if (!Schema::hasColumn('opportunities', 'expected_close_date')) {
                $table->date('expected_close_date')->nullable()->after('amount');
            }
            
            // Adicionar lead_id se não existir
            if (!Schema::hasColumn('opportunities', 'lead_id')) {
                $table->uuid('lead_id')->nullable()->after('expected_close_date');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
                $table->index('lead_id');
            }
        });

        // Renomear customer_id para cliente_id (se existir)
        if (Schema::hasColumn('opportunities', 'customer_id')) {
            Schema::table('opportunities', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'cliente_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            if (Schema::hasColumn('opportunities', 'amount')) {
                $table->renameColumn('amount', 'value');
            }
            
            if (Schema::hasColumn('opportunities', 'expected_close_date')) {
                $table->dropColumn('expected_close_date');
            }
            
            if (Schema::hasColumn('opportunities', 'lead_id')) {
                $table->dropForeign(['lead_id']);
                $table->dropIndex(['lead_id']);
                $table->dropColumn('lead_id');
            }
        });

        if (Schema::hasColumn('opportunities', 'cliente_id')) {
            Schema::table('opportunities', function (Blueprint $table) {
                $table->renameColumn('cliente_id', 'customer_id');
            });
        }
    }
};
