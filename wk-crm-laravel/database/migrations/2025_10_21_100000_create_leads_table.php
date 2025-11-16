<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('company', 255)->nullable();
            $table->string('source', 100)->nullable(); // Site, Indicação, Redes Sociais, etc
            $table->string('status', 50)->default('new'); // new, contacted, qualified, converted, lost
            $table->string('interest', 255)->nullable(); // CRM, ERP, Consultoria, etc
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['email']);
            $table->index(['source']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
