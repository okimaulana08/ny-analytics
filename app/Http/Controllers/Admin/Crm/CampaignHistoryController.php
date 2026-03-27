<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailCampaignJob;
use App\Models\EmailCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaignHistoryController extends Controller
{
    public function index(): View
    {
        $campaigns = EmailCampaign::with(['group', 'template'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.crm.campaigns.index', compact('campaigns'));
    }

    public function show(EmailCampaign $campaign): View
    {
        $campaign->load(['group', 'template']);

        $logs = $campaign->logs()->orderBy('recipient_email')->paginate(50);

        $stats = [
            'sent' => $campaign->logs()->where('status', 'sent')->count(),
            'delivered' => $campaign->logs()->where('status', 'delivered')->count(),
            'opened' => $campaign->logs()->where('status', 'opened')->count(),
            'clicked' => $campaign->logs()->where('status', 'clicked')->count(),
            'bounced' => $campaign->logs()->where('status', 'bounced')->count(),
            'failed' => $campaign->logs()->where('status', 'failed')->count(),
        ];

        return view('admin.crm.campaigns.show', compact('campaign', 'logs', 'stats'));
    }

    public function resend(EmailCampaign $campaign): RedirectResponse
    {
        if (! in_array($campaign->status, ['failed', 'sent'])) {
            return redirect()->route('admin.crm.campaigns.show', $campaign)
                ->with('error', 'Hanya campaign berstatus failed atau sent yang bisa di-resend.');
        }

        $campaign->logs()->delete();

        $campaign->update([
            'status' => 'queued',
            'sent_count' => 0,
            'failed_count' => 0,
            'sent_at' => null,
        ]);

        SendEmailCampaignJob::dispatch($campaign->id);

        return redirect()->route('admin.crm.campaigns.show', $campaign)
            ->with('success', 'Campaign dijadwalkan ulang untuk pengiriman.');
    }

    public function destroy(EmailCampaign $campaign): RedirectResponse
    {
        if (! in_array($campaign->status, ['draft', 'failed', 'scheduled'])) {
            return redirect()->route('admin.crm.campaigns.index')
                ->with('error', 'Hanya campaign berstatus draft, failed, atau scheduled yang bisa dihapus.');
        }

        $campaign->delete();

        return redirect()->route('admin.crm.campaigns.index')
            ->with('success', 'Campaign berhasil dihapus.');
    }
}
