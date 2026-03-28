<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreBroadcastEmailRequest;
use App\Jobs\SendEmailCampaignJob;
use App\Models\EmailCampaign;
use App\Models\EmailGroup;
use App\Models\EmailTemplate;
use App\Services\BrevoService;
use App\Services\ContentRecommender;
use App\Services\EmailGroupResolver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
            'sample' => array_slice(array_map(fn ($r) => ['email' => $r['email'], 'name' => $r['name']], $recipients), 0, 5),
            'members' => array_slice(array_map(fn ($r) => [
                'user_id' => $r['user_id'] ?? null,
                'email' => $r['email'],
                'name' => $r['name'] ?? '',
            ], $recipients), 0, 200),
        ]);
    }

    public function previewForUser(Request $request): Response
    {
        $template = EmailTemplate::findOrFail($request->integer('template_id'));
        $userId = $request->input('user_id');
        $userEmail = $request->input('user_email', 'demo@novelya.id');
        $userName = $request->input('user_name', 'Pengguna Demo');

        $params = [
            'name' => $userName,
            'email' => $userEmail,
            'app_url' => config('brevo.novelya_url', config('app.url')),
            'expiry_date' => now()->addDays(7)->format('d M Y'),
            'plan_name' => 'Premium Bulanan',
            'join_date' => now()->subDays(30)->format('d M Y'),
            'last_paid' => now()->subDays(60)->format('d M Y'),
            'invoice_url' => config('brevo.novelya_url', 'https://novelya.id').'/payment',
            'payment_status' => 'pending',
            'trx_count' => '1',
        ];

        if ($userId) {
            $db = DB::connection('novel');
            $user = $db->table('users')->where('id', $userId)->first(['name', 'email', 'created_at']);
            if ($user) {
                $params['name'] = $user->name ?: $params['name'];
                $params['email'] = $user->email ?: $params['email'];
                $params['join_date'] = Carbon::parse($user->created_at)->format('d M Y');
            }
            $trx = $db->table('transactions as t')
                ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
                ->where('t.user_id', $userId)->where('t.status', 'paid')
                ->orderByDesc('t.expired_at')
                ->first(['t.expired_at', 'mp.name as plan_name', 't.paid_at']);
            if ($trx) {
                $params['expiry_date'] = Carbon::parse($trx->expired_at)->format('d M Y');
                $params['plan_name'] = $trx->plan_name ?: 'Premium';
                $params['last_paid'] = $trx->paid_at ? Carbon::parse($trx->paid_at)->format('d M Y') : $params['last_paid'];
            }
        }

        $story = app(ContentRecommender::class)->getTopForUser($userId ?: null);
        if ($story) {
            $params = array_merge($params, $story);
        }

        $html = app(BrevoService::class)->renderTemplate($template->html_body, $params);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = DB::connection('novel')
            ->table('users')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($users->map(fn ($u) => [
            'user_id' => (string) $u->id,
            'email' => $u->email,
            'name' => $u->name ?? '',
        ])->values());
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
            'extra_recipients' => $this->parseExtraRecipients($data),
            'excluded_emails' => $data['excluded_emails'] ?? [],
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

    private function parseExtraRecipients(array $data): array
    {
        $emails = $data['extra_email'] ?? [];
        $names = $data['extra_name'] ?? [];
        $result = [];
        foreach ($emails as $i => $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result[] = ['email' => $email, 'name' => $names[$i] ?? ''];
            }
        }

        return $result;
    }
}
