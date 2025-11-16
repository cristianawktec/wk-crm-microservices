<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'interest')) {
                $table->string('interest', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('leads', 'city')) {
                $table->string('city', 100)->nullable()->after('interest');
            }
            if (!Schema::hasColumn('leads', 'state')) {
                $table->string('state', 2)->nullable()->after('city');
            }
            if (!Schema::hasColumn('leads', 'notes')) {
                $table->text('notes')->nullable()->after('state');
            }

            // Indexes (opcional): ignorando criação condicional para manter simples
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('leads', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('leads', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('leads', 'interest')) {
                $table->dropColumn('interest');
            }
        });
    }
};
