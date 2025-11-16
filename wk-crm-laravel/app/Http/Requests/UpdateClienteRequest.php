<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('cliente') ?? $this->route('id');
        return [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:clientes,email,' . $id,
            'telefone' => 'nullable|string|max:20',
            'documento' => 'nullable|string|max:20|unique:clientes,documento,' . $id,
            'data_nascimento' => 'nullable|date',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'status' => 'boolean',
        ];
    }
}
