<?php

namespace App\Http\Requests;

use App\Enums\OrdemServicoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->company_id !== null;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'nullable|exists:veiculos,id',
            'status' => ['required', new Enum(OrdemServicoStatus::class)],
            'description' => 'nullable|string',
            'km_entry' => 'nullable|integer',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
            'redirect_to_checklist' => 'nullable',
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
