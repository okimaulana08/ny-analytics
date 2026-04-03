<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\ScheduledEmailReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduledReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'report_type' => ['required', Rule::in([
                ScheduledEmailReport::TYPE_REVENUE_SUMMARY,
                ScheduledEmailReport::TYPE_TOP_CONTENT,
                ScheduledEmailReport::TYPE_CHURN_ALERT,
                ScheduledEmailReport::TYPE_ENGAGEMENT_SUMMARY,
            ])],
            'frequency' => ['required', Rule::in([ScheduledEmailReport::FREQ_WEEKLY, ScheduledEmailReport::FREQ_MONTHLY])],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*.email' => ['required', 'email'],
            'recipients.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
