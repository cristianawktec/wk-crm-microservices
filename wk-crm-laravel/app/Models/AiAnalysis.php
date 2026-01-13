<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $table = 'ai_analyses';

    protected $fillable = [
        'opportunity_id',
        'user_id',
        'analysis_type',
        'prompt',
        'response',
        'model',
        'tokens_used',
        'processing_time_ms',
    ];

    protected $casts = [
        'prompt' => 'array',
        'response' => 'array',
        'tokens_used' => 'integer',
        'processing_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relação com Opportunity
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Relação com User (quem fez a análise)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Extrair conclusão principal da resposta
     */
    public function getMainConclusionAttribute(): ?string
    {
        if (!$this->response || !is_array($this->response)) {
            return null;
        }

        return $this->response['risk_level'] ?? 
               $this->response['conclusion'] ?? 
               $this->response['summary'] ?? 
               null;
    }

    /**
     * Extrair nível de risco
     */
    public function getRiskLevelAttribute(): ?string
    {
        if (!$this->response || !is_array($this->response)) {
            return null;
        }

        return $this->response['risk_level'] ?? 
               $this->response['risk'] ?? 
               null;
    }
}
