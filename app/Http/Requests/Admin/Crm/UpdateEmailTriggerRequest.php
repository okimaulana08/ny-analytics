<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\EmailTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('trigger_type');

        $validConditions = match ($type) {
            EmailTrigger::TYPE_PENDING_PAYMENT => [EmailTrigger::COND_INVOICE_ACTIVE, EmailTrigger::COND_INVOICE_EXPIRED],
            EmailTrigger::TYPE_EXPIRY_REMINDER => [EmailTrigger::COND_BEFORE_EXPIRY, EmailTrigger::COND_AFTER_EXPIRY],
            default => [],
        };

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'trigger_type' => ['required', Rule::in([
                EmailTrigger::TYPE_EXPIRY_REMINDER,
                EmailTrigger::TYPE_PENDING_PAYMENT,
            ])],
            'condition' => [
                Rule::requiredIf(in_array($type, [EmailTrigger::TYPE_PENDING_PAYMENT, EmailTrigger::TYPE_EXPIRY_REMINDER])),
                'nullable',
                Rule::in(array_merge($validConditions, [''])),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'cooldown_days' => ['required', 'integer', 'min:1', 'max:365'],
            'days_before' => ['nullable', 'integer', 'min:1', 'max:30'],
            'days_after' => ['nullable', 'integer', 'min:1', 'max:30'],
            'delay_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ];
    }

    public function messages(): array
    {
        return [
            'trigger_type.in' => 'Jenis trigger tidak valid.',
            'condition.in' => 'Kondisi tidak valid untuk jenis trigger yang dipilih.',
            'subject.required' => 'Subject email tidak boleh kosong.',
            'html_body.required' => 'Isi HTML email tidak boleh kosong.',
            'cooldown_days.min' => 'Cooldown minimal 1 hari.',
        ];
    }
}
