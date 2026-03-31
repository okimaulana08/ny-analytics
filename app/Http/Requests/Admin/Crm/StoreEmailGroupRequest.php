<?php

namespace App\Http\Requests\Admin\Crm;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmailGroupRequest extends FormRequest
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
            'type' => ['required', 'in:static,dynamic'],
            'criteria' => ['required_if:type,dynamic', 'nullable', 'array'],
            'criteria.filter' => ['required_if:type,dynamic', 'string'],
            'criteria.params' => ['nullable', 'array'],
            'members' => ['required_if:type,static', 'nullable', 'array'],
            'members.*.email' => ['required_if:type,static', 'nullable', 'email', 'max:255'],
            'members.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'criteria.required_if' => 'Kriteria dinamis wajib diisi untuk tipe dynamic.',
            'criteria.filter.required_if' => 'Filter kriteria wajib dipilih.',
            'members.required_if' => 'Minimal satu anggota wajib diisi untuk tipe static.',
            'members.*.email.required' => 'Alamat email anggota wajib diisi.',
            'members.*.email.email' => 'Format email anggota tidak valid.',
        ];
    }
}
