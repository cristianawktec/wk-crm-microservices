<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sellers')) {
            Schema::create('sellers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->string('role')->nullable();
                $table->timestamps();
            });
        } else {
            // Table exists (probably created manually). Ensure expected columns exist.
            Schema::table('sellers', function (Blueprint $table) {
                if (!Schema::hasColumn('sellers', 'role')) {
                    $table->string('role')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('sellers', 'email')) {
                    $table->string('email')->nullable()->unique()->after('name');
                }
            });
        }
    }

    public function down()
    {
        // Do not drop the whole table on rollback to avoid removing pre-existing data.
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (Schema::hasColumn('sellers', 'role')) {
                    $table->dropColumn('role');
                }
                // Do not drop email/phone/name columns as they may be part of the original schema.
            });
        }
    }
}
