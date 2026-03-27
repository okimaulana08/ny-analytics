<?php

namespace App\Services;

use App\Models\EmailGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmailGroupResolver
{
    /**
     * Resolve an EmailGroup into a flat array of recipient data.
     *
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    public function resolve(EmailGroup $group): array
    {
        if ($group->type === 'static') {
            return $this->resolveStatic($group);
        }

        $criteria = $group->criteria ?? [];
        $filter = $criteria['filter'] ?? '';
        $params = $criteria['params'] ?? [];

        return match ($filter) {
            'user_baru' => $this->resolveUserBaru($params),
            'akan_expired' => $this->resolveAkanExpired($params),
            'belum_bayar' => $this->resolveBelumBayar(),
            'user_loyal' => $this->resolveUserLoyal($params),
            'baru_bayar_hari_ini' => $this->resolveBaruBayarHariIni(),
            'user_aktif' => $this->resolveUserAktif(),
            'user_gratis' => $this->resolveUserGratis(),
            'user_expired' => $this->resolveUserExpired(),
            'user_baru_minggu_ini' => $this->resolveUserBaruMingguIni(),
            'user_dorman' => $this->resolveUserDorman($params),
            'akan_expired_3hari' => $this->resolveAkanExpired(['days' => 3]),
            default => [],
        };
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserBaru(array $params): array
    {
        $days = (int) ($params['days'] ?? 30);

        return DB::connection('novel')
            ->table('users')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderByDesc('created_at')
            ->get(['email', 'name', 'created_at'])
            ->map(fn ($u) => [
                'email' => $u->email,
                'name' => $u->name ?? '',
                'params' => [
                    'name' => $u->name ?? 'Pengguna',
                    'email' => $u->email,
                    'join_date' => Carbon::parse($u->created_at)->format('d M Y'),
                ],
            ])
            ->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveAkanExpired(array $params): array
    {
        $days = (int) ($params['days'] ?? 7);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->whereBetween('t.expired_at', [now(), now()->addDays($days)])
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderBy('t.expired_at')
            ->get(['u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expiry_date' => Carbon::parse($r->expired_at)->format('d M Y'),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveBelumBayar(): array
    {
        $paidUserIds = DB::connection('novel')
            ->table('transactions')
            ->where('status', 'paid')
            ->pluck('user_id');

        return DB::connection('novel')
            ->table('users')
            ->whereNotIn('id', $paidUserIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderByDesc('created_at')
            ->get(['email', 'name', 'created_at'])
            ->map(fn ($u) => [
                'email' => $u->email,
                'name' => $u->name ?? '',
                'params' => [
                    'name' => $u->name ?? 'Pengguna',
                    'email' => $u->email,
                    'join_date' => Carbon::parse($u->created_at)->format('d M Y'),
                ],
            ])
            ->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserLoyal(array $params): array
    {
        $minTrx = (int) ($params['min_trx'] ?? 3);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.status', 'paid')
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->selectRaw('u.email, u.name, COUNT(t.id) as trx_count')
            ->groupBy('u.id', 'u.email', 'u.name')
            ->havingRaw('trx_count >= ?', [$minTrx])
            ->orderByDesc('trx_count')
            ->get();

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'trx_count' => (string) $r->trx_count,
            ],
        ])->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveBaruBayarHariIni(): array
    {
        $firstPaidToday = DB::connection('novel')
            ->table('transactions')
            ->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->select('user_id', DB::raw('MIN(paid_at) as first_paid'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) = 1');

        $rows = DB::connection('novel')
            ->table('users as u')
            ->joinSub($firstPaidToday, 'fp', 'fp.user_id', '=', 'u.id')
            ->leftJoin('membership_plans as mp', function ($j) {
                $j->join('transactions as t', function ($j2) {
                    $j2->on('t.user_id', '=', 'u.id')
                        ->where('t.status', 'paid')
                        ->whereDate('t.paid_at', today());
                })->on('mp.id', '=', 't.plan_id');
            })
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->get(['u.email', 'u.name', 'fp.first_paid']);

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'paid_at' => Carbon::parse($r->first_paid)->format('d M Y H:i'),
            ],
        ])->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserAktif(): array
    {
        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->where('t.expired_at', '>', now())
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderByDesc('t.expired_at')
            ->get(['u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expiry_date' => Carbon::parse($r->expired_at)->format('d M Y'),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * User yang tidak pernah berlangganan (tidak ada transaksi paid sama sekali).
     *
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserGratis(): array
    {
        $paidUserIds = DB::connection('novel')
            ->table('transactions')
            ->where('status', 'paid')
            ->pluck('user_id');

        return DB::connection('novel')
            ->table('users')
            ->whereNotIn('id', $paidUserIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderByDesc('created_at')
            ->get(['email', 'name', 'created_at'])
            ->map(fn ($u) => [
                'email' => $u->email,
                'name' => $u->name ?? '',
                'params' => [
                    'name' => $u->name ?? 'Pengguna',
                    'email' => $u->email,
                    'join_date' => Carbon::parse($u->created_at)->format('d M Y'),
                ],
            ])
            ->toArray();
    }

    /**
     * User yang subscriptionnya sudah expired (pernah bayar, tapi expired_at sudah lewat).
     *
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserExpired(): array
    {
        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->where('t.expired_at', '<', now())
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderByDesc('t.expired_at')
            ->get(['u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expiry_date' => Carbon::parse($r->expired_at)->format('d M Y'),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * User yang mendaftar dalam 7 hari terakhir.
     *
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserBaruMingguIni(): array
    {
        return $this->resolveUserBaru(['days' => 7]);
    }

    /**
     * User yang pernah bayar tapi tidak ada aktivitas transaksi dalam N hari terakhir.
     *
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveUserDorman(array $params): array
    {
        $days = (int) ($params['days'] ?? 90);

        $activeUserIds = DB::connection('novel')
            ->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->pluck('user_id');

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.status', 'paid')
            ->whereNotIn('u.id', $activeUserIds)
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->selectRaw('u.email, u.name, MAX(t.paid_at) as last_paid')
            ->groupBy('u.id', 'u.email', 'u.name')
            ->orderBy('last_paid')
            ->get()
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'last_paid' => $r->last_paid ? Carbon::parse($r->last_paid)->format('d M Y') : '—',
            ],
        ])->values()->toArray();
    }

    /**
     * @return array<int, array{email: string, name: string, params: array<string, string>}>
     */
    private function resolveStatic(EmailGroup $group): array
    {
        return $group->members()
            ->get()
            ->map(fn ($m) => [
                'email' => $m->email,
                'name' => $m->name ?? '',
                'params' => [
                    'name' => $m->name ?? 'Pengguna',
                    'email' => $m->email,
                ],
            ])
            ->toArray();
    }
}
