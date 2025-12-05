<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpportunitiesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('opportunities')) {
            Schema::create('opportunities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('seller_id')->nullable()->index();
                $table->decimal('value', 14, 2)->nullable();
                $table->string('currency', 10)->default('BRL');
                $table->integer('probability')->nullable();
                $table->string('status')->default('open');
                $table->date('close_date')->nullable();
                $table->timestamps();
            });
        } else {
            // Table already exists; add any missing columns this migration expects.
            Schema::table('opportunities', function (Blueprint $table) {
                if (!Schema::hasColumn('opportunities', 'client_id')) {
                    $table->uuid('client_id')->nullable()->index()->after('title');
                }
                if (!Schema::hasColumn('opportunities', 'seller_id')) {
                    $table->uuid('seller_id')->nullable()->index()->after('client_id');
                }
                if (!Schema::hasColumn('opportunities', 'value')) {
                    $table->decimal('value', 14, 2)->nullable()->after('seller_id');
                }
                if (!Schema::hasColumn('opportunities', 'currency')) {
                    $table->string('currency', 10)->default('BRL')->after('value');
                }
                if (!Schema::hasColumn('opportunities', 'probability')) {
                    $table->integer('probability')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('opportunities', 'status')) {
                    $table->string('status')->default('open')->after('probability');
                }
                if (!Schema::hasColumn('opportunities', 'close_date')) {
                    $table->date('close_date')->nullable()->after('status');
                }
            });
        }
    }

    public function down()
    {
        // Avoid dropping the entire table in production. Remove only columns added by this migration if they exist.
        if (Schema::hasTable('opportunities')) {
            Schema::table('opportunities', function (Blueprint $table) {
                $cols = ['client_id','seller_id','value','currency','probability','status','close_date'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('opportunities', $col)) {
                        // Use dropColumn safely for each existing column
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
