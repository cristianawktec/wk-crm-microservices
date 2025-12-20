<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove foreign key constraint
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        // Change column to nullable
        DB::statement('ALTER TABLE opportunities ALTER COLUMN customer_id DROP NOT NULL');

        // Re-add foreign key as nullable
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Remove nullable foreign key
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        // Change back to NOT NULL
        DB::statement('ALTER TABLE opportunities ALTER COLUMN customer_id SET NOT NULL');

        // Re-add cascade foreign key
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');
        });
    }
};
