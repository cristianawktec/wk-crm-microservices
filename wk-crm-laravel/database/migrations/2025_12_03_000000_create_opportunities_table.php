<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpportunitiesTable extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        Schema::dropIfExists('opportunities');
    }
}
