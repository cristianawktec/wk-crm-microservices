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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id')->index();
                $table->string('type'); // 'opportunity_created', 'opportunity_updated', 'opportunity_status_changed', etc
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable(); // store related data like opportunity_id
                $table->string('action_url')->nullable(); // link to redirect
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'read_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
