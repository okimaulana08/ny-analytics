<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('template_type', EmailTemplate::TYPE_CUSTOM);

        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'template_type' => ['required', Rule::in(EmailTemplate::TYPES)],

            // Custom: html_body required
            'html_body' => [
                $type === EmailTemplate::TYPE_CUSTOM ? 'required' : 'nullable',
                'string',
            ],

            // Promo settings
            'template_settings' => ['nullable', 'array'],
            'template_settings.banner_url' => [
                $type === EmailTemplate::TYPE_PROMO ? 'required' : 'nullable',
                'nullable', 'string', 'max:500',
            ],
            'template_settings.promo_headline' => [
                $type === EmailTemplate::TYPE_PROMO ? 'required' : 'nullable',
                'nullable', 'string', 'max:255',
            ],
            'template_settings.promo_body' => ['nullable', 'string', 'max:1000'],
            'template_settings.cta_url' => [
                $type === EmailTemplate::TYPE_PROMO ? 'required' : 'nullable',
                'nullable', 'string', 'max:500',
            ],
            'template_settings.cta_text' => [
                $type === EmailTemplate::TYPE_PROMO ? 'required' : 'nullable',
                'nullable', 'string', 'max:100',
            ],
            'template_settings.promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
