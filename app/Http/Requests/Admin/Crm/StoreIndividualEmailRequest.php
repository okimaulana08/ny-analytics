<?php

namespace App\Http\Requests\Admin\Crm;

use Illuminate\Foundation\Http\FormRequest;

class StoreIndividualEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_email' => ['required', 'email', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'template_id' => ['required', 'integer', 'exists:sqlite.email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_email.required' => 'Alamat email penerima wajib diisi.',
            'recipient_email.email' => 'Format email tidak valid.',
            'template_id.exists' => 'Template email tidak ditemukan.',
            'scheduled_at.after' => 'Waktu jadwal harus di masa depan.',
        ];
    }
}
