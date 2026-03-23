@extends('layouts.admin')
@section('title', 'Aktivitas User')
@section('page-title', 'Aktivitas User — Register, Login & Akses')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Total User</p>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi->total_users) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">+{{ number_format($kpi->new_30d) }} bulan ini</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Register Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($kpi->new_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">+{{ number_format($kpi->new_7d) }} minggu ini</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Login Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($kpi->logins_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->logins_7d) }} unik 7 hari</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Baca Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($kpi->active_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->never_logged_in) }} belum pernah login</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">

    {{-- Trend 30 hari --}}
    <div class="glass-card p-5 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Registrasi & Login — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">User baru dan aktivitas login harian</p>
            </div>
        </div>
        <div class="relative h-48"><canvas id="trendChart"></canvas></div>
    </div>

    {{-- Aktif per jam hari ini --}}
    <div class="glass-card p-5">
        <div class="mb-4">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Aktif per Jam — Hari Ini</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Berdasarkan aktivitas baca chapter</p>
        </div>
        <div class="relative h-48"><canvas id="hourChart"></canvas></div>
    </div>
</div>

{{-- User Table --}}
<div class="flat-card">
    {{-- Header & Filter --}}
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Daftar User</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ number_format($total) }} user</p>
        </div>
        {{-- Filter tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-white/[0.04]">
            @foreach(['all' => 'Semua', 'new' => 'Baru (7h)', 'subscribed' => 'Berlangganan', 'never_login' => 'Belum Login'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['filter' => $val, 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $filter === $val ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Register</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Login Terakhir</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Baca Terakhir</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Login</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan Aktif</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Expires</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            @php
                $lastActivity = $u->last_read_at ?? $u->last_login_at;
                $daysSince    = $lastActivity ? \Carbon\Carbon::parse($lastActivity)->diffInDays(now()) : null;
            @endphp
            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                            @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-center">
                    @if($u->has_membership)
                        <span class="badge badge-paid">Member</span>
                    @elseif($u->last_login_at)
                        <span class="badge badge-expired">User Gratis</span>
                    @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600 font-mono">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-500 whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($u->registered_at)->format('d M Y') }}
                    @if(\Carbon\Carbon::parse($u->registered_at)->diffInDays(now()) <= 7)
                        <div class="text-[10px] text-emerald-500 font-semibold">Baru</div>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap {{ !$u->last_login_at ? 'text-red-400' : 'text-slate-400' }}">
                    @if($u->last_login_at)
                        {{ \Carbon\Carbon::parse($u->last_login_at)->format('d M Y') }}
                        <div class="text-[10px] text-slate-300 dark:text-slate-600">{{ \Carbon\Carbon::parse($u->last_login_at)->format('H:i') }}</div>
                    @else
                        <span class="text-red-400">Belum login</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap">
                    @if($u->last_read_at)
                        @php
                            $lr      = \Carbon\Carbon::parse($u->last_read_at);
                            $diffMin = (int) $lr->diffInMinutes(now());
                            $diffHr  = (int) $lr->diffInHours(now());
                            $diffDay = (int) $lr->diffInDays(now());
                            if ($diffMin < 1)       { $ago = 'Baru saja';            $cls = 'text-emerald-600 dark:text-emerald-400 font-semibold'; }
                            elseif ($diffMin < 60)  { $ago = $diffMin . ' mnt lalu'; $cls = 'text-emerald-600 dark:text-emerald-400 font-semibold'; }
                            elseif ($diffHr < 24)   { $ago = $diffHr . ' jam lalu';  $cls = 'text-blue-500 dark:text-blue-400 font-semibold'; }
                            elseif ($diffDay == 1)  { $ago = 'Kemarin';              $cls = 'text-blue-400'; }
                            elseif ($diffDay <= 7)  { $ago = $diffDay . ' hari lalu';$cls = 'text-slate-500 dark:text-slate-400'; }
                            else                    { $ago = $diffDay . ' hari lalu';$cls = 'text-slate-400'; }
                        @endphp
                        <span class="{{ $cls }}">{{ $ago }}</span>
                        <div class="text-[10px] text-slate-300 dark:text-slate-600">{{ $lr->format('d M H:i') }}</div>
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-mono text-xs font-semibold {{ $u->login_count > 0 ? 'text-slate-700 dark:text-slate-200' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $u->login_count > 0 ? $u->login_count . '×' : '—' }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    {{ $u->active_plan ?? '—' }}
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap">
                    @if($u->membership_expires_at)
                        @php $daysLeft = \Carbon\Carbon::parse($u->membership_expires_at)->diffInDays(now(), false); @endphp
                        <span class="{{ $daysLeft > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ \Carbon\Carbon::parse($u->membership_expires_at)->format('d M Y') }}
                        </span>
                        @if($daysLeft <= 0)
                            <div class="text-[10px] text-emerald-500">{{ abs((int)$daysLeft) }}h lagi</div>
                        @elseif($daysLeft <= 7)
                            <div class="text-[10px] text-red-400">{{ (int)$daysLeft }}h lalu</div>
                        @endif
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <button onclick="openRecModal('{{ $u->id }}', {{ json_encode($u->name) }}, {{ json_encode($u->phone_number) }})"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-violet-50 dark:bg-violet-500/10 hover:bg-violet-100 dark:hover:bg-violet-500/20 text-violet-700 dark:text-violet-300 transition-colors text-[11px] font-semibold whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Rec.
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-slate-400">Tidak ada data user.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @include('admin.partials.pagination', [
        'page'       => $page,
        'totalPages' => $totalPages,
        'total'      => $total,
        'perPage'    => $perPage,
        'param'      => 'page',
    ])
</div>

{{-- ── Recommendation Modal ─────────────────────────────────────────────── --}}
<div id="rec-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRecModal()"></div>

    {{-- Panel --}}
    <div class="absolute inset-y-0 right-0 w-full max-w-xl flex flex-col bg-white dark:bg-slate-900 shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-start justify-between px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex-shrink-0">
            <div id="rec-user-info" class="flex items-center gap-3">
                <div id="rec-avatar" class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0"></div>
                <div>
                    <div id="rec-name" class="text-sm font-semibold text-slate-800 dark:text-white"></div>
                    <div id="rec-email" class="text-[11px] text-slate-400 font-mono"></div>
                    <div id="rec-phone-display" class="text-[11px] text-slate-400 font-mono hidden"></div>
                </div>
            </div>
            <button onclick="closeRecModal()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-400 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Loading --}}
        <div id="rec-loading" class="flex-1 flex items-center justify-center gap-3 text-slate-400">
            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-sm">Menganalisis riwayat baca...</span>
        </div>

        {{-- Body --}}
        <div id="rec-body" class="hidden flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">

                {{-- Stats row --}}
                <div id="rec-stats" class="grid grid-cols-3 gap-3"></div>

                {{-- Top categories --}}
                <div id="rec-categories-wrap">
                    <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-2">Genre Favorit</p>
                    <div id="rec-categories" class="flex flex-wrap gap-2"></div>
                </div>

                {{-- Recent reads --}}
                <div id="rec-recent-wrap">
                    <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-2">Baca Terakhir</p>
                    <div id="rec-recent" class="space-y-1.5"></div>
                </div>

                {{-- Recommendations --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Rekomendasi Cerita</p>
                        <span class="text-[10px] bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-300 px-2 py-0.5 rounded-full font-semibold">AI-assisted</span>
                    </div>
                    <div id="rec-list" class="space-y-3"></div>
                </div>

                {{-- WA Template --}}
                <div id="rec-wa-wrap" class="pt-2 border-t border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Template Pesan WA</p>
                        <button onclick="resetWaTemplate()" class="text-[10px] text-slate-400 hover:text-violet-600 transition-colors">Reset</button>
                    </div>
                    <div class="text-[10px] text-slate-400 mb-2 flex flex-wrap gap-1.5">
                        <span class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded font-mono">{nama}</span>
                        <span class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded font-mono">{genre_favorit}</span>
                        <span class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded font-mono">{judul_1}</span>
                        <span class="bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-300 px-1.5 py-0.5 rounded font-mono">{link_1}</span>
                        <span class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded font-mono">{judul_2}</span>
                        <span class="bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-300 px-1.5 py-0.5 rounded font-mono">{link_2}</span>
                        <span class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded font-mono">{judul_3}</span>
                        <span class="bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-300 px-1.5 py-0.5 rounded font-mono">{link_3}</span>
                    </div>
                    <textarea id="rec-wa-template" rows="12"
                        class="w-full text-[12px] font-mono bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-200 resize-none focus:outline-none focus:ring-2 focus:ring-violet-500/30 leading-relaxed"></textarea>
                    <div class="mt-1 text-[10px] text-slate-400" id="rec-wa-preview-label">Preview: <span id="rec-wa-char-count">0</span> karakter</div>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="px-5 py-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-3 flex-shrink-0">
                <button onclick="copyWaMessage()" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-white/[0.10] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] text-xs font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Salin Pesan
                </button>
                <div id="rec-wa-btn-wrap">
                    {{-- rendered by JS if phone available --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark      = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const ttDefaults = {
    backgroundColor: dark ? '#1e293b' : '#fff',
    titleColor: dark ? '#f1f5f9' : '#1e293b',
    bodyColor:  dark ? '#94a3b8' : '#64748b',
    borderColor: dark ? '#334155' : '#e2e8f0',
    borderWidth: 1, padding: 10, cornerRadius: 10
};

// ── Trend Chart ──────────────────────────────────────────────────────────────
const trendRaw = @json($trendData);
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendRaw.map(d => {
            const dt = new Date(d.date);
            return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        }),
        datasets: [
            {
                label: 'Registrasi Baru',
                data: trendRaw.map(d => d.reg),
                borderColor: dark ? '#34d399' : '#10b981',
                backgroundColor: dark ? 'rgba(52,211,153,0.12)' : 'rgba(16,185,129,0.08)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 5,
            },
            {
                label: 'Login',
                data: trendRaw.map(d => d.logins),
                borderColor: dark ? '#60a5fa' : '#3b82f6',
                backgroundColor: dark ? 'rgba(96,165,250,0.08)' : 'rgba(59,130,246,0.06)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 5,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: true, labels: { color: tickColor, font: { size: 11 }, boxWidth: 12 } },
            tooltip: { ...ttDefaults }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 }, maxTicksLimit: 10 }, border: { display: false } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false }, beginAtZero: true }
        }
    }
});

// ── Recommendation Modal ─────────────────────────────────────────────────────
const REC_ROUTE_BASE = '{{ route('admin.reports.user-recommend', ['userId' => '__ID__']) }}';
const DEFAULT_TEMPLATE = `Halo {nama}! 👋

Berdasarkan genre favoritmu ({genre_favorit}), kami punya rekomendasi cerita seru untukmu:

📖 {judul_1}
🔗 {link_1}

📖 {judul_2}
🔗 {link_2}

📖 {judul_3}
🔗 {link_3}

Langsung baca di aplikasi Novelya ya! Jangan sampai ketinggalan cerita seru 🎉`;

let _recPhone = null;
let _recData  = null;

function openRecModal(userId, name, phone) {
    _recPhone = phone || null;
    _recData  = null;

    document.getElementById('rec-avatar').textContent  = (name || 'U').charAt(0).toUpperCase();
    document.getElementById('rec-name').textContent    = name || '—';
    document.getElementById('rec-email').textContent   = '';
    const pd = document.getElementById('rec-phone-display');
    if (phone) { pd.textContent = phone; pd.classList.remove('hidden'); }
    else        { pd.classList.add('hidden'); }

    document.getElementById('rec-loading').classList.remove('hidden');
    document.getElementById('rec-body').classList.add('hidden');
    document.getElementById('rec-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    fetch(REC_ROUTE_BASE.replace('__ID__', encodeURIComponent(userId)))
        .then(r => r.json())
        .then(data => {
            _recData = data;
            renderRecModal(data);
        })
        .catch(err => {
            document.getElementById('rec-loading').innerHTML =
                `<span class="text-red-400 text-sm">Gagal memuat data: ${err.message}</span>`;
        });
}

function closeRecModal() {
    document.getElementById('rec-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function renderRecModal(data) {
    const { user, top_categories, recent_reads, recommendations } = data;

    document.getElementById('rec-email').textContent = user.email || '';

    // Stats
    document.getElementById('rec-stats').innerHTML = [
        { label: 'Total Buku',    value: Number(user.total_books    ?? 0).toLocaleString('id-ID'), color: 'violet' },
        { label: 'Total Chapter', value: Number(user.total_chapters ?? 0).toLocaleString('id-ID'), color: 'blue' },
        { label: 'Status',        value: user.has_membership ? 'Member' : 'Gratis',                color: user.has_membership ? 'emerald' : 'amber' },
    ].map(s => `
        <div class="flat-card p-3 text-center">
            <div class="font-mono text-lg font-bold text-${s.color}-600 dark:text-${s.color}-400">${s.value}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">${s.label}</div>
        </div>`).join('');

    // Top categories
    const cats = top_categories || [];
    document.getElementById('rec-categories-wrap').style.display = cats.length ? '' : 'none';
    document.getElementById('rec-categories').innerHTML = cats.map(c => `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-[11px] font-semibold">
            ${esc(c.name)}
            <span class="text-[10px] opacity-60">${c.read_count}×</span>
        </span>`).join('');

    // Recent reads
    const recent = recent_reads || [];
    document.getElementById('rec-recent-wrap').style.display = recent.length ? '' : 'none';
    document.getElementById('rec-recent').innerHTML = recent.map(r => {
        const dt  = new Date(r.created_at);
        const ago = formatAgo(dt);
        return `<div class="flex items-center gap-2.5 py-1.5 border-b border-slate-50 dark:border-white/[0.04] last:border-0">
            <div class="w-1.5 h-1.5 rounded-full bg-violet-400 flex-shrink-0"></div>
            <div class="flex-1 min-w-0">
                <div class="text-[12px] text-slate-700 dark:text-slate-200 font-medium truncate">${esc(r.title)}</div>
                <div class="text-[10px] text-slate-400">${esc(r.category || '—')}</div>
            </div>
            <div class="text-[10px] text-slate-400 flex-shrink-0">${ago}</div>
        </div>`;
    }).join('');

    // Recommendations
    const recs = recommendations || [];
    document.getElementById('rec-list').innerHTML = recs.length
        ? recs.map((r, i) => {
            const stars = r.rating > 0 ? '★'.repeat(Math.round(r.rating)) + '☆'.repeat(5 - Math.round(r.rating)) : '';
            const tags  = r.tags ? r.tags.split(',').slice(0, 3).map(t => `<span class="px-1.5 py-0.5 bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 rounded text-[10px]">${esc(t.trim())}</span>`).join('') : '';
            return `<div class="flat-card p-3.5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-mono font-bold text-xs shadow-sm">${i+1}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-slate-800 dark:text-white leading-snug mb-0.5">${esc(r.title)}</div>
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="text-[10px] font-semibold text-violet-600 dark:text-violet-300 bg-violet-50 dark:bg-violet-500/10 px-1.5 py-0.5 rounded">${esc(r.category || '—')}</span>
                            ${stars ? `<span class="text-[10px] text-amber-500 tracking-tighter">${stars}</span>` : ''}
                            <span class="text-[10px] text-slate-400">${Number(r.read_count).toLocaleString('id-ID')} baca</span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400">${Number(r.subscribe_count).toLocaleString('id-ID')} subscribe</span>
                        </div>
                        ${r.synopsis ? `<p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2 mb-1.5">${esc(r.synopsis)}</p>` : ''}
                        ${tags ? `<div class="flex flex-wrap gap-1">${tags}</div>` : ''}
                    </div>
                </div>
            </div>`;
        }).join('')
        : `<div class="text-center py-6 text-slate-400 text-sm">Tidak ada rekomendasi ditemukan.</div>`;

    // WA template
    const topGenre = cats.length ? cats[0].name : 'favoritmu';
    const titles   = recs.map(r => r.title);
    const tmpl = localStorage.getItem('rec_wa_template') || DEFAULT_TEMPLATE;
    document.getElementById('rec-wa-template').value = tmpl;
    updateWaCharCount();

    // Send WA button
    const wrap = document.getElementById('rec-wa-btn-wrap');
    if (_recPhone) {
        wrap.innerHTML = `<button onclick="sendWaRecommend()" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Kirim ke WA
        </button>`;
    } else {
        wrap.innerHTML = `<span class="text-[11px] text-slate-400 bg-slate-100 dark:bg-white/[0.04] px-3 py-2 rounded-lg">No HP tidak tersedia</span>`;
    }

    document.getElementById('rec-loading').classList.add('hidden');
    document.getElementById('rec-body').classList.remove('hidden');
}

function buildWaMessage() {
    if (!_recData) { return ''; }
    const { user, top_categories, recommendations } = _recData;
    const tmpl  = document.getElementById('rec-wa-template').value;
    const recs  = recommendations || [];
    const genre = (top_categories || []).map(c => c.name).join(', ') || 'favoritmu';
    const link  = slug => slug ? `https://novelya.id/detail/${slug}` : '';
    return tmpl
        .replace(/\{nama\}/g,         user.name || '')
        .replace(/\{genre_favorit\}/g, genre)
        .replace(/\{judul_1\}/g,      recs[0]?.title || '')
        .replace(/\{link_1\}/g,       link(recs[0]?.slug))
        .replace(/\{judul_2\}/g,      recs[1]?.title || '')
        .replace(/\{link_2\}/g,       link(recs[1]?.slug))
        .replace(/\{judul_3\}/g,      recs[2]?.title || '')
        .replace(/\{link_3\}/g,       link(recs[2]?.slug));
}

function updateWaCharCount() {
    const txt = document.getElementById('rec-wa-template').value;
    document.getElementById('rec-wa-char-count').textContent = txt.length;
    localStorage.setItem('rec_wa_template', txt);
}

function resetWaTemplate() {
    document.getElementById('rec-wa-template').value = DEFAULT_TEMPLATE;
    localStorage.removeItem('rec_wa_template');
    updateWaCharCount();
}

function copyWaMessage() {
    const msg = buildWaMessage();
    navigator.clipboard.writeText(msg).then(() => {
        const btn = event.currentTarget;
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

function sendWaRecommend() {
    if (!_recPhone) { return; }
    const msg  = buildWaMessage();
    const phone = _recPhone.replace(/\D/g, '').replace(/^0/, '62');
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
}

function esc(s) {
    if (!s) { return ''; }
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatAgo(dt) {
    const diffMs  = Date.now() - dt.getTime();
    const diffMin = Math.floor(diffMs / 60000);
    if (diffMin < 1)   { return 'Baru saja'; }
    if (diffMin < 60)  { return diffMin + ' mnt lalu'; }
    const diffHr = Math.floor(diffMin / 60);
    if (diffHr < 24)   { return diffHr + ' jam lalu'; }
    const diffDay = Math.floor(diffHr / 24);
    if (diffDay === 1) { return 'Kemarin'; }
    return diffDay + ' hari lalu';
}

document.getElementById('rec-wa-template').addEventListener('input', updateWaCharCount);

// ── Active by Hour Chart ─────────────────────────────────────────────────────
const hourRaw = @json($activeByHour);
const hourLabels = Array.from({length: 24}, (_, i) => i + ':00');
const hourData   = Array(24).fill(0);
hourRaw.forEach(d => { hourData[d.hr] = d.cnt; });

new Chart(document.getElementById('hourChart'), {
    type: 'bar',
    data: {
        labels: hourLabels,
        datasets: [{
            label: 'User Aktif',
            data: hourData,
            backgroundColor: dark ? 'rgba(139,92,246,0.5)' : 'rgba(124,58,237,0.35)',
            borderRadius: 4, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.raw} user aktif` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 9 }, maxTicksLimit: 8 }, border: { display: false } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false }, beginAtZero: true }
        }
    }
});
</script>
@endpush
