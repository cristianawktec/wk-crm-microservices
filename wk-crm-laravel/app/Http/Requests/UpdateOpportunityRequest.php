<?php

namespace App\Http\Requests;

use App\Domain\Opportunity\Opportunity;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Mapeamento PT-BR → EN
        $this->merge([
            'title' => $this->input('titulo') ?? $this->input('title'),
            'description' => $this->input('descricao') ?? $this->input('description'),
            'amount' => $this->input('valor') ?? $this->input('amount'),
            'expected_close_date' => $this->input('data_prevista_fechamento') ?? $this->input('expected_close_date'),
            'status' => $this->input('status'),
            'lead_id' => $this->input('lead_id'),
            'cliente_id' => $this->input('cliente_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', 'required', 'string', 'in:' . implode(',', Opportunity::VALID_STATUSES)],
            'lead_id' => ['nullable', 'uuid', 'exists:leads,id'],
            'cliente_id' => ['nullable', 'uuid', 'exists:customers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da oportunidade é obrigatório',
            'title.max' => 'O título não pode ter mais de 255 caracteres',
            'amount.required' => 'O valor da oportunidade é obrigatório',
            'amount.numeric' => 'O valor deve ser um número válido',
            'amount.min' => 'O valor não pode ser negativo',
            'expected_close_date.date' => 'A data prevista deve ser uma data válida',
            'expected_close_date.after_or_equal' => 'A data prevista não pode ser no passado',
            'status.required' => 'O status é obrigatório',
            'status.in' => 'Status inválido',
            'lead_id.uuid' => 'ID do lead inválido',
            'lead_id.exists' => 'Lead não encontrado',
            'cliente_id.uuid' => 'ID do cliente inválido',
            'cliente_id.exists' => 'Cliente não encontrado',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Se está atualizando os relacionamentos, validar que ao menos um existe
            if ($this->has('lead_id') || $this->has('cliente_id')) {
                if (empty($this->lead_id) && empty($this->cliente_id)) {
                    $validator->errors()->add(
                        'lead_id',
                        'A oportunidade deve estar associada a um Lead ou Cliente'
                    );
                }
            }
        });
    }
}
