<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueToOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add `value` column only if it does not exist yet to avoid errors
        if (!Schema::hasColumn('opportunities', 'value')) {
            Schema::table('opportunities', function (Blueprint $table) {
                $table->decimal('value', 14, 2)->default(0)->after('title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('opportunities', 'value')) {
            Schema::table('opportunities', function (Blueprint $table) {
                $table->dropColumn('value');
            });
        }
    }
}
