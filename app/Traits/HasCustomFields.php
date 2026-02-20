<?php

namespace App\Traits;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasCustomFields
{
    /**
     * Relacionamento para obter os valores dos campos personalizados deste registro.
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    /**
     * Busca as definições de campos personalizados disponíveis para este tipo de entidade e empresa.
     */
    public function getAvailableCustomFields()
    {
        // Pega o nome da classe sem o namespace para usar como entity_type
        $entityType = class_basename($this);
        $companyId = $this->company_id ?? Auth::user()->company_id ?? null;

        if (!$companyId) return collect();

        return CustomField::where('company_id', $companyId)
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Retorna os campos com seus respectivos valores para este registro.
     */
    public function getCustomFieldsWithValues()
    {
        $fields = $this->getAvailableCustomFields();
        $values = $this->customFieldValues->pluck('value', 'custom_field_id');

        return $fields->map(function ($field) use ($values) {
            $field->current_value = $values->get($field->id);
            return $field;
        });
    }

    /**
     * Salva ou atualiza os valores dos campos personalizados.
     * Espera um array: ['field_id' => 'valor', ...]
     */
    public function syncCustomFields(array $fieldsData)
    {
        foreach ($fieldsData as $fieldId => $value) {
            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $fieldId,
                    'model_id' => $this->id,
                    'model_type' => get_class($this),
                ],
                ['value' => $value]
            );
        }
    }
}
