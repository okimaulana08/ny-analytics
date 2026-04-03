<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunicationLogController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->input('channel');
        $subType = $request->input('sub_type');
        $search = $request->input('search');
        $from = $request->input('from');
        $to = $request->input('to');
        $perPage = in_array((int) $request->input('per_page'), [25, 50, 100]) ? (int) $request->input('per_page') : 25;

        $rows = collect();

        // 1. Email Trigger Logs
        if (! $channel || $channel === 'Email') {
            if (! $subType || $subType === 'Trigger') {
                $q = DB::connection('sqlite')
                    ->table('email_trigger_logs as etl')
                    ->join('email_triggers as et', 'et.id', '=', 'etl.email_trigger_id')
                    ->select(
                        'etl.sent_at',
                        'etl.user_id',
                        'etl.recipient_email as identifier',
                        'etl.recipient_name',
                        'etl.status',
                        DB::raw("'Email' as channel"),
                        DB::raw("'Trigger' as sub_type"),
                        'et.name as source_name'
                    )
                    ->whereNotNull('etl.sent_at');

                if ($from) {
                    $q->where('etl.sent_at', '>=', $from.' 00:00:00');
                }
                if ($to) {
                    $q->where('etl.sent_at', '<=', $to.' 23:59:59');
                }

                $rows = $rows->merge($q->get());
            }

            if (! $subType || $subType === 'Broadcast') {
                $q = DB::connection('sqlite')
                    ->table('email_campaign_logs as ecl')
                    ->join('email_campaigns as ec', 'ec.id', '=', 'ecl.email_campaign_id')
                    ->select(
                        'ecl.sent_at',
                        DB::raw('null as user_id'),
                        'ecl.recipient_email as identifier',
                        'ecl.recipient_name',
                        'ecl.status',
                        DB::raw("'Email' as channel"),
                        DB::raw("'Broadcast' as sub_type"),
                        'ec.name as source_name'
                    )
                    ->whereNotNull('ecl.sent_at');

                if ($from) {
                    $q->where('ecl.sent_at', '>=', $from.' 00:00:00');
                }
                if ($to) {
                    $q->where('ecl.sent_at', '<=', $to.' 23:59:59');
                }

                $rows = $rows->merge($q->get());
            }
        }

        // 2. WA Trigger Logs
        if (! $channel || $channel === 'WA') {
            if (! $subType || $subType === 'Trigger') {
                $q = DB::connection('sqlite')
                    ->table('wa_trigger_logs as wtl')
                    ->join('wa_triggers as wt', 'wt.id', '=', 'wtl.wa_trigger_id')
                    ->select(
                        'wtl.sent_at',
                        'wtl.user_id',
                        'wtl.phone as identifier',
                        DB::raw('null as recipient_name'),
                        DB::raw("'sent' as status"),
                        DB::raw("'WA' as channel"),
                        DB::raw("'Trigger' as sub_type"),
                        'wt.name as source_name'
                    );

                if ($from) {
                    $q->where('wtl.sent_at', '>=', $from.' 00:00:00');
                }
                if ($to) {
                    $q->where('wtl.sent_at', '<=', $to.' 23:59:59');
                }

                $rows = $rows->merge($q->get());
            }

            if (! $subType || $subType === 'Notifikasi') {
                $q = DB::connection('sqlite')
                    ->table('wa_notifications')
                    ->select(
                        'sent_at',
                        DB::raw('null as user_id'),
                        'transaction_id as identifier',
                        DB::raw('null as recipient_name'),
                        DB::raw("'sent' as status"),
                        DB::raw("'WA' as channel"),
                        DB::raw("'Notifikasi' as sub_type"),
                        DB::raw("CASE type WHEN 'pending' THEN 'WA Pending Reminder' ELSE 'WA Paid Notifikasi' END as source_name")
                    );

                if ($from) {
                    $q->where('sent_at', '>=', $from.' 00:00:00');
                }
                if ($to) {
                    $q->where('sent_at', '<=', $to.' 23:59:59');
                }

                $rows = $rows->merge($q->get());
            }
        }

        // Enrich user names from novel DB
        $userIds = $rows->pluck('user_id')->filter()->unique()->values();
        $userMap = [];
        if ($userIds->isNotEmpty()) {
            $userMap = DB::connection('novel')
                ->table('users')
                ->whereIn('id', $userIds->toArray())
                ->pluck('name', 'id')
                ->toArray();
        }

        $rows = $rows->map(function ($row) use ($userMap) {
            $row->user_name = $row->user_id ? ($userMap[$row->user_id] ?? null) : null;

            return $row;
        });

        // Apply search filter after enrichment
        if ($search) {
            $search = strtolower($search);
            $rows = $rows->filter(function ($row) use ($search) {
                return str_contains(strtolower((string) ($row->identifier ?? '')), $search)
                    || str_contains(strtolower((string) ($row->recipient_name ?? '')), $search)
                    || str_contains(strtolower((string) ($row->user_name ?? '')), $search)
                    || str_contains(strtolower((string) ($row->source_name ?? '')), $search);
            });
        }

        // Sort and paginate
        $sorted = $rows->sortByDesc('sent_at')->values();
        $total = $sorted->count();
        $page = $request->input('page', 1);
        $items = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $logs = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.communication-logs.index', compact('logs', 'perPage', 'channel', 'subType', 'search', 'from', 'to'));
    }

    public function frequencyMonitor(Request $request): View
    {
        $threshold7d = AppConfig::get('comms_max_7d', 3);
        $threshold30d = AppConfig::get('comms_max_30d', 10);
        $since7 = now()->subDays(7)->toDateTimeString();
        $since30 = now()->subDays(30)->toDateTimeString();
        $search = $request->input('search');
        $onlyOver = $request->boolean('only_over');

        // --- Email counts per email address ---
        $emailRows = DB::connection('sqlite')
            ->table(DB::raw('(
                SELECT recipient_email as contact, sent_at FROM email_trigger_logs WHERE sent_at IS NOT NULL
                UNION ALL
                SELECT recipient_email as contact, sent_at FROM email_campaign_logs WHERE sent_at IS NOT NULL
            ) as t'))
            ->select(
                'contact',
                DB::raw('COUNT(*) as email_30d'),
                DB::raw("SUM(CASE WHEN sent_at >= '{$since7}' THEN 1 ELSE 0 END) as email_7d")
            )
            ->where('sent_at', '>=', $since30)
            ->groupBy('contact')
            ->get()
            ->keyBy('contact');

        // --- WA counts per phone ---
        $waRows = DB::connection('sqlite')
            ->table('wa_trigger_logs')
            ->select(
                'phone as contact',
                'user_id',
                DB::raw('COUNT(*) as wa_30d'),
                DB::raw("SUM(CASE WHEN sent_at >= '{$since7}' THEN 1 ELSE 0 END) as wa_7d")
            )
            ->where('sent_at', '>=', $since30)
            ->groupBy('phone', 'user_id')
            ->get();

        // --- Collect all unique identifiers (email + user_id) ---
        $allEmails = $emailRows->keys()->toArray();
        $allUserIds = $waRows->pluck('user_id')->filter()->unique()->toArray();

        // Enrich emails → user info from novel DB
        $usersByEmail = DB::connection('novel')
            ->table('users')
            ->whereIn('email', $allEmails)
            ->select('id', 'email', 'name')
            ->get()
            ->keyBy('email');

        $usersByUid = $allUserIds
            ? DB::connection('novel')
                ->table('users')
                ->whereIn('id', $allUserIds)
                ->select('id', 'email', 'name')
                ->get()
                ->keyBy('id')
            : collect();

        // Build unified user list keyed by email
        $users = collect();

        foreach ($emailRows as $email => $eRow) {
            $novelUser = $usersByEmail->get($email);
            $users->put($email, [
                'name' => $novelUser?->name ?? $eRow->contact,
                'email' => $email,
                'phone' => null,
                'email_7d' => (int) $eRow->email_7d,
                'email_30d' => (int) $eRow->email_30d,
                'wa_7d' => 0,
                'wa_30d' => 0,
            ]);
        }

        foreach ($waRows as $waRow) {
            $novelUser = $waRow->user_id ? $usersByUid->get($waRow->user_id) : null;
            $email = $novelUser?->email ?? '';
            $name = $novelUser?->name ?? $waRow->contact;

            if ($email && $users->has($email)) {
                $existing = $users->get($email);
                $existing['wa_7d'] += (int) $waRow->wa_7d;
                $existing['wa_30d'] += (int) $waRow->wa_30d;
                $existing['phone'] = $waRow->contact;
                $users->put($email, $existing);
            } else {
                $key = $email ?: 'phone:'.$waRow->contact;
                $users->put($key, [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $waRow->contact,
                    'email_7d' => 0,
                    'email_30d' => 0,
                    'wa_7d' => (int) $waRow->wa_7d,
                    'wa_30d' => (int) $waRow->wa_30d,
                ]);
            }
        }

        // Compute totals and alert level
        $users = $users->map(function ($u) use ($threshold7d, $threshold30d) {
            $u['total_7d'] = $u['email_7d'] + $u['wa_7d'];
            $u['total_30d'] = $u['email_30d'] + $u['wa_30d'];
            $u['alert'] = match (true) {
                $u['total_7d'] > $threshold7d || $u['total_30d'] > $threshold30d => 'red',
                $u['total_7d'] >= (int) ceil($threshold7d * 0.7) => 'yellow',
                default => 'green',
            };

            return $u;
        });

        // Filters
        if ($search) {
            $s = strtolower($search);
            $users = $users->filter(fn ($u) => str_contains(strtolower($u['name']), $s)
                || str_contains(strtolower($u['email']), $s)
                || str_contains(strtolower((string) $u['phone']), $s)
            );
        }

        if ($onlyOver) {
            $users = $users->filter(fn ($u) => $u['alert'] !== 'green');
        }

        $users = $users->sortByDesc('total_7d')->values();

        return view('admin.communication-logs.frequency', compact(
            'users', 'threshold7d', 'threshold30d', 'search', 'onlyOver'
        ));
    }
}
