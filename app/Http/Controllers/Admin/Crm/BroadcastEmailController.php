<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreBroadcastEmailRequest;
use App\Jobs\SendEmailCampaignJob;
use App\Models\EmailCampaign;
use App\Models\EmailGroup;
use App\Models\EmailTemplate;
use App\Services\EmailGroupResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastEmailController extends Controller
{
    public function create(): View
    {
        $groups = EmailGroup::where('is_active', true)->orderBy('name')->get();
        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.crm.broadcast.create', compact('groups', 'templates'));
    }

    public function preview(Request $request): JsonResponse
    {
        $group = EmailGroup::find($request->input('group_id'));

        if (! $group) {
            return response()->json(['count' => 0, 'sample' => []]);
        }

        $resolver = app(EmailGroupResolver::class);
        $recipients = $resolver->resolve($group);

        return response()->json([
            'count' => count($recipients),
            'sample' => array_slice(array_map(fn ($r) => [
                'email' => $r['email'],
                'name' => $r['name'],
            ], $recipients), 0, 5),
        ]);
    }

    public function store(StoreBroadcastEmailRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isScheduled = ! empty($data['scheduled_at']);

        $campaign = EmailCampaign::create([
            'name' => $data['name'],
            'email_group_id' => $data['group_id'],
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
            ->with('success', 'Campaign broadcast berhasil '.($isScheduled ? 'dijadwalkan.' : 'dikirim ke antrian.'));
    }
}
