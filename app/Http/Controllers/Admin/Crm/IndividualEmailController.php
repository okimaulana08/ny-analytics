<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreIndividualEmailRequest;
use App\Jobs\SendEmailCampaignJob;
use App\Models\EmailCampaign;
use App\Models\EmailGroup;
use App\Models\EmailGroupMember;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IndividualEmailController extends Controller
{
    public function create(): View
    {
        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.crm.individual.create', compact('templates'));
    }

    public function store(StoreIndividualEmailRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isScheduled = ! empty($data['scheduled_at']);

        // Create a temporary static group for this individual send
        $group = EmailGroup::create([
            'name' => 'Individual: '.$data['recipient_email'].' @ '.now()->format('Y-m-d H:i'),
            'type' => 'static',
            'is_active' => false,
        ]);

        EmailGroupMember::create([
            'email_group_id' => $group->id,
            'email' => $data['recipient_email'],
            'name' => $data['recipient_name'] ?? '',
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'Individual: '.$data['recipient_email'],
            'email_group_id' => $group->id,
            'email_template_id' => $data['template_id'],
            'subject' => $data['subject'],
            'sender_email' => config('brevo.sender_email'),
            'sender_name' => config('brevo.sender_name'),
            'status' => $isScheduled ? 'scheduled' : 'queued',
            'scheduled_at' => $isScheduled ? $data['scheduled_at'] : null,
            'created_by' => session('admin_id'),
        ]);

        if ($isScheduled) {
            $delaySeconds = max(0, now()->diffInSeconds($campaign->scheduled_at, false) - 120);
            SendEmailCampaignJob::dispatch($campaign->id)->delay($delaySeconds);
        } else {
            SendEmailCampaignJob::dispatch($campaign->id);
        }

        return redirect()->route('admin.crm.campaigns.index')
            ->with('success', 'Email individual berhasil '.($isScheduled ? 'dijadwalkan.' : 'dikirim ke antrian.'));
    }
}
