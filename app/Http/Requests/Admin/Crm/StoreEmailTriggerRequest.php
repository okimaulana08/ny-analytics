<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\EmailTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'trigger_type' => ['required', Rule::in([
                EmailTrigger::TYPE_EXPIRY_REMINDER,
                EmailTrigger::TYPE_RE_ENGAGEMENT,
                EmailTrigger::TYPE_WELCOME_PAYMENT,
            ])],
            'email_template_id' => ['required', 'exists:email_templates,id'],
            'cooldown_days' => ['required', 'integer', 'min:1', 'max:365'],
            'days_before' => ['nullable', 'integer', 'min:1', 'max:30'],
            'inactive_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function messages(): array
    {
        return [
            'trigger_type.in' => 'Jenis trigger tidak valid.',
            'email_template_id.exists' => 'Template email tidak ditemukan.',
            'cooldown_days.min' => 'Cooldown minimal 1 hari.',
        ];
    }
}
