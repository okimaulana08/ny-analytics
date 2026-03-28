<?php

namespace App\Http\Requests\Admin\Crm;

use Illuminate\Foundation\Http\FormRequest;

class StoreBroadcastEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['required', 'integer', 'exists:sqlite.email_groups,id'],
            'template_id' => ['required', 'integer', 'exists:sqlite.email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'extra_email' => ['nullable', 'array'],
            'extra_email.*' => ['nullable', 'email'],
            'extra_name' => ['nullable', 'array'],
            'extra_name.*' => ['nullable', 'string', 'max:255'],
            'excluded_emails' => ['nullable', 'array'],
            'excluded_emails.*' => ['nullable', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.exists' => 'Grup email tidak ditemukan.',
            'template_id.exists' => 'Template email tidak ditemukan.',
            'scheduled_at.after' => 'Waktu jadwal harus di masa depan.',
        ];
    }
}
