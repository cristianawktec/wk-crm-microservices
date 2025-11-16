<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mapped = [];
        
        if ($this->has('titulo') && !$this->has('title')) {
            $mapped['title'] = $this->input('titulo');
        }
        if ($this->has('descricao') && !$this->has('description')) {
            $mapped['description'] = $this->input('descricao');
        }
        if ($this->has('valor') && !$this->has('value')) {
            $mapped['value'] = $this->input('valor');
        }
        if ($this->has('cliente_id') && !$this->has('customer_id')) {
            $mapped['customer_id'] = $this->input('cliente_id');
        }
        
        if (!empty($mapped)) {
            $this->merge($mapped);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'status' => ['nullable', 'in:open,negotiation,won,lost'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório',
            'value.required' => 'O valor é obrigatório',
            'value.numeric' => 'O valor deve ser numérico',
            'customer_id.required' => 'O cliente é obrigatório',
            'customer_id.exists' => 'Cliente não encontrado',
            'status.in' => 'Status inválido',
        ];
    }
}
