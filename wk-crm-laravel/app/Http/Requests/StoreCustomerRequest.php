<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation para criação de Customer
 * Centraliza regras de validação (Single Responsibility - SOLID)
 */
class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar com Sanctum
    }

    /**
     * Prepara os dados antes da validação - mapeia campos PT-BR para EN
     */
    protected function prepareForValidation(): void
    {
        $mapped = [];
        
        // Mapeia campos em português para inglês se não existir o campo em inglês
        if ($this->has('nome') && !$this->has('name')) {
            $mapped['name'] = $this->input('nome');
        }
        
        if ($this->has('telefone') && !$this->has('phone')) {
            $mapped['phone'] = $this->input('telefone');
        }
        
        if ($this->has('endereco') && !$this->has('address')) {
            $mapped['address'] = $this->input('endereco');
        }
        
        if ($this->has('cidade') && !$this->has('city')) {
            $mapped['city'] = $this->input('cidade');
        }
        
        if ($this->has('estado') && !$this->has('state')) {
            $mapped['state'] = $this->input('estado');
        }
        
        if ($this->has('cep') && !$this->has('postal_code')) {
            $mapped['postal_code'] = $this->input('cep');
        }
        
        if (!empty($mapped)) {
            $this->merge($mapped);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'postal_code' => ['nullable', 'string', 'max:9'],
        ];
    }

    /**
     * Mensagens de validação em português
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.unique' => 'Este email já está cadastrado',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres',
            'state.max' => 'O estado deve ter 2 caracteres',
            'postal_code.max' => 'O CEP não pode ter mais de 9 caracteres',
        ];
    }
}
