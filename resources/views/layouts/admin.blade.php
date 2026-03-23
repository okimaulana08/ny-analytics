<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Novelya Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        mono: ['"Fira Code"', 'monospace'],
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono, h1, h2, h3, .font-heading { font-family: 'Fira Code', monospace; }

        /* Sidebar nav links */
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500;
            color: rgba(255,255,255,0.55);
            transition: all 0.15s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.9); }
        .nav-link.active { background: rgba(255,255,255,0.12); color: #fff; }

        /* Glass card */
        .glass-card {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226,232,240,0.8);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 1px 4px rgba(0,0,0,0.04);
        }
        .dark .glass-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            box-shadow: 0 1px 3px rgba(0,0,0,0.2), 0 4px 12px rgba(0,0,0,0.15);
        }
        .dark .glass-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 1px 4px rgba(0,0,0,0.2);
        }

        /* Flat card (for tables) */
        .flat-card {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 16px;
            overflow: hidden;
        }
        .dark .flat-card {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.07);
        }

        /* Status badges */
        .badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.01em; }
        .badge-paid    { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-expired { background: #f1f5f9; color: #64748b; }
        .badge-failed  { background: #fee2e2; color: #991b1b; }
        .dark .badge-paid    { background: rgba(16,185,129,0.15); color: #34d399; }
        .dark .badge-pending { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .dark .badge-expired { background: rgba(100,116,139,0.15); color: #94a3b8; }
        .dark .badge-failed  { background: rgba(239,68,68,0.15);  color: #f87171; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="h-full bg-slate-100 dark:bg-[#0d0f14] text-slate-900 dark:text-slate-100 transition-colors duration-300">

<div class="flex h-full min-h-screen">

    {{-- Mobile overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-30 w-60 flex-shrink-0 flex flex-col bg-[#0f172a] dark:bg-[#080a10] -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-white/[0.06]">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/[0.06]">
            <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <div class="font-mono text-white font-semibold text-sm leading-tight">Novelya</div>
                <div class="text-white/30 text-xs">Analytics</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <div class="text-white/20 text-[10px] font-semibold px-3 py-2 uppercase tracking-widest">Menu</div>

            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <div class="text-white/20 text-[10px] font-semibold px-3 py-2 mt-3 uppercase tracking-widest">Manajemen</div>

            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                User Admin
            </a>

            <div class="text-white/20 text-[10px] font-semibold px-3 py-2 mt-3 uppercase tracking-widest">Laporan CRM</div>

            <a href="{{ route('admin.reports.subscription') }}" class="nav-link {{ request()->routeIs('admin.reports.subscription') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Subscription
            </a>

            <a href="{{ route('admin.reports.engagement') }}" class="nav-link {{ request()->routeIs('admin.reports.engagement') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Engagement
            </a>

            <a href="{{ route('admin.reports.segments') }}" class="nav-link {{ request()->routeIs('admin.reports.segments') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Segmen User
            </a>

            <a href="{{ route('admin.reports.transactions') }}" class="nav-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Transaksi
            </a>

            <a href="{{ route('admin.reports.realtime') }}" class="nav-link {{ request()->routeIs('admin.reports.realtime') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
                </svg>
                Realtime
            </a>

            <a href="{{ route('admin.reports.user-activity') }}" class="nav-link {{ request()->routeIs('admin.reports.user-activity') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Aktivitas User
            </a>

            <a href="{{ route('admin.reports.content') }}" class="nav-link {{ request()->routeIs('admin.reports.content') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Performa Konten
            </a>

            <a href="{{ route('admin.reports.acquisition') }}" class="nav-link {{ request()->routeIs('admin.reports.acquisition') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                Akuisisi & Referral
            </a>

            <div class="text-white/20 text-[10px] font-semibold px-3 py-2 mt-3 uppercase tracking-widest">Assistant</div>

            <a href="{{ route('admin.assistant.stakeholder') }}" class="nav-link {{ request()->routeIs('admin.assistant.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Stakeholder
            </a>
        </nav>

        {{-- User info --}}
        <div class="px-3 pb-4 border-t border-white/[0.06] pt-3">
            <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-white/[0.05]">
                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                    {{ strtoupper(substr(session('admin_user.name', 'A'), 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-xs font-medium truncate">{{ session('admin_user.name', 'Admin') }}</div>
                    <div class="text-white/30 text-[11px] truncate">{{ session('admin_user.email', '') }}</div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-white/30 hover:text-white/70 transition-colors cursor-pointer" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="sticky top-0 z-10 bg-slate-100/80 dark:bg-[#0d0f14]/80 backdrop-blur-md border-b border-slate-200/60 dark:border-white/[0.06] px-4 lg:px-6 py-3 flex items-center gap-4">

            {{-- Hamburger --}}
            <button onclick="toggleSidebar()" class="lg:hidden w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-200 dark:hover:bg-white/5 flex items-center justify-center transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1">
                <h1 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2">
                {{-- Clock --}}
                <div class="hidden sm:block text-xs text-slate-400 dark:text-slate-500 font-mono tabular-nums" id="current-time"></div>

                {{-- Dark mode --}}
                <button onclick="toggleDark()" class="w-8 h-8 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/5 flex items-center justify-center transition-colors cursor-pointer" title="Toggle dark mode">
                    <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">

            @if(session('success'))
            <div class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-500/20 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded-xl border border-red-200 dark:border-red-500/20 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleDark() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }
    (function() {
        const t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }

    (function clock() {
        const el = document.getElementById('current-time');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        setTimeout(clock, 1000);
    })();
</script>
{{-- ===== GLOBAL USER DETAIL MODAL ===== --}}
<div id="user-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeUserModal()"></div>
    {{-- Panel --}}
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl flex flex-col bg-white dark:bg-[#111827] shadow-2xl transform translate-x-full transition-transform duration-300 ease-out" id="user-modal-panel">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-white/[0.07] flex-shrink-0">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white" id="user-modal-title">Detail User</h2>
                <p class="text-[11px] text-slate-400 mt-0.5" id="user-modal-subtitle"></p>
            </div>
            <button onclick="closeUserModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] flex items-center justify-center transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        {{-- Search --}}
        <div class="px-6 py-3 border-b border-slate-100 dark:border-white/[0.05] flex-shrink-0">
            <input id="user-modal-search" type="text" placeholder="Cari nama atau email..." oninput="filterModalUsers()"
                class="w-full h-9 px-3 text-sm rounded-xl bg-slate-100 dark:bg-white/[0.06] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 transition">
        </div>
        {{-- Loading state --}}
        <div id="user-modal-loading" class="flex-1 flex items-center justify-center hidden">
            <div class="flex items-center gap-3 text-slate-400">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span class="text-sm">Memuat data...</span>
            </div>
        </div>
        {{-- Table --}}
        <div id="user-modal-body" class="flex-1 overflow-y-auto hidden">
            <table class="w-full text-sm" id="user-modal-table">
                <thead class="sticky top-0 bg-slate-50 dark:bg-[#0d1117] z-10">
                    <tr id="user-modal-thead-row"></tr>
                </thead>
                <tbody id="user-modal-tbody"></tbody>
            </table>
        </div>
        {{-- Empty state --}}
        <div id="user-modal-empty" class="flex-1 flex items-center justify-center hidden">
            <p class="text-sm text-slate-400">Tidak ada data untuk segmen ini.</p>
        </div>
    </div>
</div>

<script>
// ===== User modal logic =====
let _modalAllRows = [];

function openUserModal(segment, title, subtitle) {
    const modal = document.getElementById('user-modal');
    const panel = document.getElementById('user-modal-panel');
    modal.classList.remove('hidden');
    document.getElementById('user-modal-title').textContent = title;
    document.getElementById('user-modal-subtitle').textContent = subtitle || '';
    document.getElementById('user-modal-search').value = '';
    showModalState('loading');
    // Animate in
    requestAnimationFrame(() => panel.classList.remove('translate-x-full'));

    fetch(`{{ route('admin.reports.users') }}?segment=${segment}`)
        .then(r => r.json())
        .then(data => {
            _modalAllRows = data.users || [];
            document.getElementById('user-modal-subtitle').textContent =
                (subtitle || '') + ` — ${data.count} user`;
            renderModalTable(_modalAllRows, segment);
        })
        .catch(() => showModalState('empty'));
}

function closeUserModal() {
    const panel = document.getElementById('user-modal-panel');
    const modal = document.getElementById('user-modal');
    panel.classList.add('translate-x-full');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function showModalState(state) {
    ['loading','body','empty'].forEach(s => {
        document.getElementById('user-modal-' + s).classList.toggle('hidden', s !== state);
    });
}

function renderModalTable(users, segment) {
    if (!users.length) { showModalState('empty'); return; }
    showModalState('body');

    // Build columns based on segment
    const cols = {
        paid_users:      [{k:'name',l:'Nama'},{k:'email',l:'Email'},{k:'phone_number',l:'No. HP'},{k:'trx_count',l:'Trx',align:'right'},{k:'total_spent',l:'Total Belanja',align:'right',fmt:'rp'},{k:'last_paid_at',l:'Terakhir Bayar',fmt:'date'}],
        free_users:      [{k:'name',l:'Nama'},{k:'email',l:'Email'},{k:'phone_number',l:'No. HP'},{k:'last_login_at',l:'Login Terakhir',fmt:'date'},{k:'created_at',l:'Daftar',fmt:'date'}],
        renewers:        [{k:'name',l:'Nama'},{k:'email',l:'Email'},{k:'phone_number',l:'No. HP'},{k:'trx_count',l:'Renew',align:'right'},{k:'total_spent',l:'Total',align:'right',fmt:'rp'},{k:'last_paid_at',l:'Terakhir Bayar',fmt:'date'}],
        active_readers:  [{k:'name',l:'Nama'},{k:'email',l:'Email'},{k:'phone_number',l:'No. HP'},{k:'total_reads',l:'Reads',align:'right'},{k:'active_days',l:'Hari Aktif',align:'right'},{k:'last_read_at',l:'Baca Terakhir',fmt:'date'}],
        repeat_readers:  [{k:'name',l:'Nama'},{k:'email',l:'Email'},{k:'phone_number',l:'No. HP'},{k:'active_days',l:'Hari Aktif',align:'right'},{k:'total_reads',l:'Total Reads',align:'right'},{k:'last_read_at',l:'Baca Terakhir',fmt:'date'}],
    };
    const def = cols[segment] || [{k:'name',l:'Nama'},{k:'email',l:'Email'}];

    // Header
    const thRow = document.getElementById('user-modal-thead-row');
    thRow.innerHTML = def.map(c =>
        `<th class="px-5 py-3 text-${c.align||'left'} text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">${c.l}</th>`
    ).join('');

    // Body
    const tbody = document.getElementById('user-modal-tbody');
    tbody.innerHTML = users.map((u, i) => {
        const cells = def.map(c => {
            let val = u[c.k] ?? '—';
            if (c.fmt === 'rp' && val !== '—') val = 'Rp ' + Number(val).toLocaleString('id-ID');
            if (c.fmt === 'date' && val && val !== '—') val = new Date(val).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'});
            return `<td class="px-5 py-2.5 text-${c.align||'left'} ${c.align==='right'?'font-mono text-xs':'text-xs'} text-slate-700 dark:text-slate-300 whitespace-nowrap">${val}</td>`;
        }).join('');
        return `<tr data-row="${i}" class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">${cells}</tr>`;
    }).join('');
}

function filterModalUsers() {
    const q = document.getElementById('user-modal-search').value.toLowerCase();
    document.querySelectorAll('#user-modal-tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Close on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserModal(); });
</script>

@stack('scripts')
</body>
</html>
