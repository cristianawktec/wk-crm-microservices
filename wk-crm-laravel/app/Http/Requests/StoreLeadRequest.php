<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mapped = [];
        
        if ($this->has('nome') && !$this->has('name')) {
            $mapped['name'] = $this->input('nome');
        }
        if ($this->has('telefone') && !$this->has('phone')) {
            $mapped['phone'] = $this->input('telefone');
        }
        if ($this->has('empresa') && !$this->has('company')) {
            $mapped['company'] = $this->input('empresa');
        }
        if ($this->has('origem') && !$this->has('source')) {
            $mapped['source'] = $this->input('origem');
        }
        
        if (!empty($mapped)) {
            $this->merge($mapped);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:new,contacted,qualified,lost'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.email' => 'O email deve ser válido',
            'status.in' => 'Status inválido',
        ];
    }
}
