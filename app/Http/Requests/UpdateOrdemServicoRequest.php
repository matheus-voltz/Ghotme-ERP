<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'required|exists:veiculos,id',
            'status' => 'required',
            'description' => 'nullable|string',
            'km_entry' => 'nullable|integer',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.exists' => 'O cliente selecionado é inválido.',
            'veiculo_id.required' => 'O veículo é obrigatório.',
            'veiculo_id.exists' => 'O veículo selecionado é inválido.',
            'status.required' => 'O status é obrigatório.',
        ];
    }
}
