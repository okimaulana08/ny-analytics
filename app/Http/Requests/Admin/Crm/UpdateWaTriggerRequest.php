<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\WaTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWaTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $validConditions = match ($type) {
            WaTrigger::TYPE_PENDING_PAYMENT => [WaTrigger::COND_INVOICE_ACTIVE, WaTrigger::COND_INVOICE_EXPIRED],
            WaTrigger::TYPE_EXPIRY_REMINDER => [WaTrigger::COND_BEFORE_EXPIRY, WaTrigger::COND_AFTER_EXPIRY],
            default => [],
        };

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([WaTrigger::TYPE_PENDING_PAYMENT, WaTrigger::TYPE_EXPIRY_REMINDER])],
            'condition' => ['required', Rule::in($validConditions)],
            'delay_value' => ['required', 'integer', 'min:1', 'max:999'],
            'delay_unit' => ['required', Rule::in(['minutes', 'hours', 'days'])],
            'cooldown_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'templates' => ['nullable', 'array'],
            'templates.*.id' => ['nullable', 'integer'],
            'templates.*.body' => ['required_with:templates', 'string', 'max:2000'],
            'templates.*.is_active' => ['nullable', 'boolean'],
            'delete_template_ids' => ['nullable', 'array'],
            'delete_template_ids.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Jenis trigger tidak valid.',
            'condition.in' => 'Kondisi tidak valid untuk jenis trigger yang dipilih.',
            'delay_unit.in' => 'Satuan delay tidak valid.',
            'cooldown_hours.min' => 'Cooldown minimal 1 jam.',
            'templates.*.body.required_with' => 'Isi pesan template tidak boleh kosong.',
        ];
    }
}
