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
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            
            // Relações
            $table->unsignedBigInteger('opportunity_id');
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Dados da análise
            $table->string('analysis_type')->default('risk_assessment'); // risk_assessment, trend_analysis, etc
            $table->longText('prompt')->nullable(); // JSON com dados enviados
            $table->longText('response')->nullable(); // JSON com resposta da IA
            $table->string('model')->default('gemini-pro'); // Qual modelo foi usado
            
            // Metadados
            $table->integer('tokens_used')->nullable(); // Tokens consumidos
            $table->integer('processing_time_ms')->nullable(); // Tempo de processamento em ms
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->foreign('opportunity_id')->references('id')->on('opportunities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('opportunity_id');
            $table->index('user_id');
            $table->index('analysis_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
