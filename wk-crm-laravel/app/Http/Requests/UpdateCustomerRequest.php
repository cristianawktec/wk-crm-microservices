<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation para atualização de Customer
 */
class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar com Sanctum
    }

    /**
     * Get the validation rules that apply to the request.
     */
    protected function prepareForValidation(): void
    {
        $mapped = [];
        if ($this->has('nome') && !$this->has('name')) { $mapped['name'] = $this->input('nome'); }
        if ($this->has('telefone') && !$this->has('phone')) { $mapped['phone'] = $this->input('telefone'); }
        if ($this->has('endereco') && !$this->has('address')) { $mapped['address'] = $this->input('endereco'); }
        if ($this->has('cidade') && !$this->has('city')) { $mapped['city'] = $this->input('cidade'); }
        if ($this->has('estado') && !$this->has('state')) { $mapped['state'] = $this->input('estado'); }
        if ($this->has('cep') && !$this->has('postal_code')) { $mapped['postal_code'] = $this->input('cep'); }
        if (!empty($mapped)) { $this->merge($mapped); }
    }

    public function rules(): array
    {
        $customerId = $this->route('cliente') ?? $this->route('customer') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'postal_code' => ['nullable', 'string', 'max:9'],
            'status' => ['sometimes', 'in:active,inactive'],
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
            'status.in' => 'O status deve ser active ou inactive',
        ];
    }
}
