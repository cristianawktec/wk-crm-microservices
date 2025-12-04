<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerIdToLeadsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'seller_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->uuid('seller_id')->nullable()->index()->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'seller_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('seller_id');
            });
        }
    }
}
