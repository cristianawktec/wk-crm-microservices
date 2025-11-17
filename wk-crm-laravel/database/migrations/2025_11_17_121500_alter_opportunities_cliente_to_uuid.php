<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only proceed if column exists and is not already uuid in PostgreSQL
        if (Schema::hasColumn('opportunities', 'cliente_id')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'pgsql') {
                // Drop FK to clientes if exists
                try { \DB::statement('ALTER TABLE opportunities DROP CONSTRAINT opportunities_cliente_id_foreign'); } catch (\Throwable $e) {}
                // Alter type bigint -> uuid (table assumed empty or values not critical)
                \DB::statement('ALTER TABLE opportunities ALTER COLUMN cliente_id TYPE uuid USING NULL');
            } else {
                Schema::table('opportunities', function (Blueprint $table) {
                    $table->uuid('cliente_id')->nullable()->change();
                });
            }
            // Recreate FK to customers
            Schema::table('opportunities', function (Blueprint $table) {
                $table->foreign('cliente_id')->references('id')->on('customers')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        // Drop FK to customers
        try { \DB::statement('ALTER TABLE opportunities DROP CONSTRAINT opportunities_cliente_id_foreign'); } catch (\Throwable $e) {}
        if ($driver === 'pgsql') {
            \DB::statement('ALTER TABLE opportunities ALTER COLUMN cliente_id TYPE bigint USING NULL');
        } else {
            Schema::table('opportunities', function (Blueprint $table) {
                $table->unsignedBigInteger('cliente_id')->nullable()->change();
            });
        }
        // FK back to clientes
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
        });
    }
};
