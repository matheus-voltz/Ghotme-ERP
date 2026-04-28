<?php

namespace App\Http\Requests;

use App\Enums\OrdemServicoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $os = $this->route('ordem_servico');
        return $this->user() !== null
            && $os !== null
            && $os->company_id === $this->user()->company_id;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'required|exists:veiculos,id',
            'status' => ['required', new Enum(OrdemServicoStatus::class)],
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
