@extends('layouts.admin')

@section('title', 'Realtime Dashboard')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800 dark:text-white">Realtime Dashboard</h1>
            <p class="text-sm text-slate-400 mt-0.5">Aktivitas baca user dalam rentang waktu terakhir</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Diperbarui: <span id="last-updated">{{ $generatedAt }} WIB</span></span>
            </div>
            <button onclick="location.reload()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Time Window Tabs --}}
    <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-200/60 dark:bg-white/[0.04] w-fit">
        @foreach([1 => '1 Jam', 6 => '6 Jam', 24 => '24 Jam'] as $h => $label)
        <button onclick="switchTab({{ $h }})"
            id="tab-btn-{{ $h }}"
            class="tab-btn px-5 py-1.5 rounded-lg text-sm font-medium transition-all
                   {{ $h === 1 ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white' }}">
            {{ $label }} Terakhir
        </button>
        @endforeach
    </div>

    {{-- Tab Panels --}}
    @foreach([1, 6, 24] as $h)
    @php
        $tab            = $tabData[$h];
        $kpi            = $tab['kpi'];
        $users          = $tab['users'];
        $books          = $tab['bookDetails'];
        $anonUsers      = $tab['anonUsers'];
        $anonBookDets   = $tab['anonBookDetails'];

        // Compute totals from both registered + anonymous (for KPI cards on initial load)
        $computedChapters       = array_sum(array_column(array_map(fn($u) => (array)$u, $users), 'chapters_read'));
        $computedUniqueChapters = array_sum(array_column(array_map(fn($u) => (array)$u, $users), 'unique_chapters_read'));
        $allContentIds          = array_merge(...(array_map(fn($u) => array_column($books[$u->id] ?? [], 'content_id'), $users) ?: [[]]));
        $computedBooks          = count(array_unique($allContentIds));

        // Collect unique books for dropdown (registered + anon)
        $allBooks = [];
        foreach ($books as $userBookList) {
            foreach ($userBookList as $b) { $allBooks[$b->content_id] = $b->title; }
        }
        foreach ($anonBookDets as $anonBookList) {
            foreach ($anonBookList as $b) { $allBooks[$b->content_id] = $b->title; }
        }
        asort($allBooks);
    @endphp
    <div id="tab-panel-{{ $h }}" class="space-y-5 {{ $h !== 1 ? 'hidden' : '' }}">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass-card p-5">
                <p class="text-xs text-slate-400 mb-1">User Aktif</p>
                <p id="kpi-users-{{ $h }}" class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format(count($users)) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    <span id="kpi-users-breakdown-{{ $h }}">{{ number_format($kpi->active_users) }} login · {{ number_format($kpi->anon_sessions) }} anon</span>
                </p>
            </div>
            <div class="glass-card p-5">
                <p class="text-xs text-slate-400 mb-1">Total Chapter Dibaca</p>
                <p id="kpi-chapters-{{ $h }}" class="text-2xl font-bold text-blue-500">{{ number_format($computedChapters) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    <span id="kpi-unique-chapters-{{ $h }}" class="text-blue-400 font-medium">{{ number_format($computedUniqueChapters) }} unik</span>
                    · {{ $h }} jam terakhir
                </p>
            </div>
            <div class="glass-card p-5">
                <p class="text-xs text-slate-400 mb-1">Konten Diakses</p>
                <p id="kpi-books-{{ $h }}" class="text-2xl font-bold text-violet-500">{{ number_format($computedBooks) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">judul unik</p>
            </div>
            <div class="glass-card p-5">
                <p class="text-xs text-slate-400 mb-1">Transaksi Paid</p>
                <p class="text-2xl font-bold text-emerald-500">{{ number_format($kpi->paid_tx) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">berhasil bayar</p>
            </div>
        </div>

        {{-- User Table --}}
        <div class="flat-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.05] space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white">
                        Daftar User Aktif
                        <span id="user-count-{{ $h }}" class="ml-1.5 px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 text-xs font-normal">
                            {{ count($users) }} user
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400">Klik baris untuk lihat detail buku</p>
                </div>

                {{-- Filters row --}}
                <div class="flex flex-wrap items-center gap-2">

                    {{-- User-type filter --}}
                    <div class="flex items-center gap-0.5 p-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                        @foreach(['reg' => 'Terdaftar', 'anon' => 'Anonymous', 'all' => 'Semua'] as $type => $label)
                        <button onclick="filterByUserType({{ $h }}, '{{ $type }}')"
                            id="ut-btn-{{ $h }}-{{ $type }}"
                            class="px-3 py-1 text-xs font-medium rounded-md transition-all
                                   {{ $type === 'reg'
                                       ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm'
                                       : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Book filter --}}
                    @if(!empty($allBooks))
                    <div class="relative flex-1 max-w-xs">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <select id="book-filter-{{ $h }}"
                            onchange="filterByBook({{ $h }}, this.value)"
                            class="w-full pl-8 pr-8 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.05] text-slate-700 dark:text-slate-200 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Buku ({{ count($allBooks) }})</option>
                            @foreach($allBooks as $cid => $title)
                            <option value="{{ $cid }}">{{ Str::limit($title, 55) }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <button onclick="filterByBook({{ $h }}, ''); document.getElementById('book-filter-{{ $h }}').value = '';"
                        class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
                        Reset
                    </button>
                    @endif
                </div>
            </div>

            @php $hasAnyData = !empty($users) || !empty($anonUsers); @endphp

            @if(!$hasAnyData)
            <div class="px-5 py-12 text-center text-sm text-slate-400">
                Tidak ada aktivitas dalam {{ $h }} jam terakhir.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                            <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Langganan</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Chapter</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Buku</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Trx Periode Ini</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Aktivitas Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">

                        {{-- ── Registered users ── --}}
                        @foreach($users as $user)
                        @php
                            $userBooks = $books[$user->id] ?? [];
                            $hasExpiry = !empty($user->membership_expires);
                            $isActive  = $hasExpiry && \Carbon\Carbon::parse($user->membership_expires)->isFuture();
                            $isExpired = $hasExpiry && !$isActive;
                            $neverSubs = !$user->ever_paid;
                        @endphp

                        {{-- Main Row --}}
                        <tr class="user-row-{{ $h }} hover:bg-slate-50 dark:hover:bg-white/[0.02] cursor-pointer transition-colors group"
                            data-user-type="registered"
                            data-book-ids="{{ implode(',', array_column($userBooks, 'content_id')) }}"
                            data-chapters="{{ $user->chapters_read }}"
                            data-unique-chapters="{{ $user->unique_chapters_read }}"
                            data-books="{{ $user->books_count }}"
                            data-expand-id="books-{{ $h }}-{{ $user->id }}"
                            onclick="toggleBooks('{{ $h }}-{{ $user->id }}')">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-white leading-tight">{{ $user->name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">{{ $user->email ?? '—' }}</p>
                                        @if($user->phone_number)
                                        <p class="text-xs text-slate-400">{{ $user->phone_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($isActive)
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        s/d {{ \Carbon\Carbon::parse($user->membership_expires)->format('d M Y') }}
                                    </span>
                                </div>
                                @elseif($isExpired)
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Expired
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($user->membership_expires)->format('d M Y') }}
                                    </span>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 text-xs">
                                    Belum pernah
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->chapters_read }}</span>
                                @if($user->unique_chapters_read < $user->chapters_read)
                                <div class="text-[10px] text-blue-400 mt-0.5">{{ $user->unique_chapters_read }} unik</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->books_count }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($user->paid_tx > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    {{ $user->paid_tx }}× paid
                                </span>
                                @else
                                <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}
                                    </span>
                                    <svg id="chevron-{{ $h }}-{{ $user->id }}"
                                        class="w-4 h-4 text-slate-400 transition-transform duration-200 group-hover:text-slate-600 dark:group-hover:text-slate-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </td>
                        </tr>

                        {{-- Expand: Book List --}}
                        <tr id="books-{{ $h }}-{{ $user->id }}" class="hidden bg-slate-50/80 dark:bg-white/[0.015]"
                            data-expand-for="registered">
                            <td colspan="6" class="px-5 py-3">
                                @if(empty($userBooks))
                                <p class="text-xs text-slate-400 italic">Tidak ada data buku.</p>
                                @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($userBooks as $book)
                                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-white dark:bg-white/[0.04] border border-slate-200/70 dark:border-white/[0.06]">
                                        <div class="w-7 h-7 rounded-md bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate" title="{{ $book->title }}">
                                                {{ $book->title }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                {{ $book->chapters_read }} chapter
                                                @if($book->unique_chapters_read < $book->chapters_read)
                                                <span class="text-blue-400">({{ $book->unique_chapters_read }} unik)</span>
                                                @endif
                                                · terakhir {{ \Carbon\Carbon::parse($book->last_read)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        {{-- ── Anonymous sessions (hidden by default) ── --}}
                        @foreach($anonUsers as $anon)
                        @php
                            $anonBooks = $anonBookDets[$anon->session_id] ?? [];
                            $anonKey   = 'anon-' . $h . '-' . md5($anon->session_id);
                            $shortSess = strtoupper(substr($anon->session_id, 0, 8));
                        @endphp

                        <tr class="user-row-{{ $h }} hidden hover:bg-slate-50 dark:hover:bg-white/[0.02] cursor-pointer transition-colors group"
                            data-user-type="anonymous"
                            data-book-ids="{{ implode(',', array_column($anonBooks, 'content_id')) }}"
                            data-chapters="{{ $anon->chapters_read }}"
                            data-unique-chapters="{{ $anon->unique_chapters_read }}"
                            data-books="{{ $anon->books_count }}"
                            data-expand-id="{{ $anonKey }}"
                            onclick="toggleBooks('{{ $anonKey }}')">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-600 dark:text-slate-300 leading-tight font-mono text-xs">
                                            Sesi #{{ $shortSess }}
                                        </p>
                                        <p class="text-xs text-slate-400">—</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 text-xs">
                                    Anonymous
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $anon->chapters_read }}</span>
                                @if($anon->unique_chapters_read < $anon->chapters_read)
                                <div class="text-[10px] text-blue-400 mt-0.5">{{ $anon->unique_chapters_read }} unik</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $anon->books_count }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs text-slate-400">—</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($anon->last_activity)->diffForHumans() }}
                                    </span>
                                    <svg id="chevron-{{ $anonKey }}"
                                        class="w-4 h-4 text-slate-400 transition-transform duration-200 group-hover:text-slate-600 dark:group-hover:text-slate-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </td>
                        </tr>

                        {{-- Expand: Anonymous book list --}}
                        <tr id="{{ $anonKey }}" class="hidden bg-slate-50/80 dark:bg-white/[0.015]"
                            data-expand-for="anonymous">
                            <td colspan="6" class="px-5 py-3">
                                @if(empty($anonBooks))
                                <p class="text-xs text-slate-400 italic">Tidak ada data buku.</p>
                                @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($anonBooks as $book)
                                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-white dark:bg-white/[0.04] border border-slate-200/70 dark:border-white/[0.06]">
                                        <div class="w-7 h-7 rounded-md bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate" title="{{ $book->title }}">
                                                {{ $book->title }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                {{ $book->chapters_read }} chapter
                                                @if($book->unique_chapters_read < $book->chapters_read)
                                                <span class="text-blue-400">({{ $book->unique_chapters_read }} unik)</span>
                                                @endif
                                                · terakhir {{ \Carbon\Carbon::parse($book->last_read)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>
    @endforeach

</div>
@endsection

@push('scripts')
<script>
    // ── User-type filter state per tab (default: registered) ──
    const userTypeFilter = { 1: 'reg', 6: 'reg', 24: 'reg' };
    const bookFilterState = { 1: '', 6: '', 24: '' };

    // ── Tab switching ──
    function switchTab(h) {
        [1, 6, 24].forEach(t => {
            document.getElementById('tab-panel-' + t).classList.add('hidden');
            const btn = document.getElementById('tab-btn-' + t);
            btn.classList.remove('bg-white', 'dark:bg-white/10', 'text-slate-800', 'dark:text-white', 'shadow-sm');
            btn.classList.add('text-slate-500', 'dark:text-slate-400');
        });
        document.getElementById('tab-panel-' + h).classList.remove('hidden');
        const active = document.getElementById('tab-btn-' + h);
        active.classList.add('bg-white', 'dark:bg-white/10', 'text-slate-800', 'dark:text-white', 'shadow-sm');
        active.classList.remove('text-slate-500', 'dark:text-slate-400');
    }

    // ── Expand/collapse book detail rows ──
    function toggleBooks(key) {
        // Registered: expand row id = "books-{h}-{userId}", chevron id = "chevron-{h}-{userId}"
        // Anonymous:  expand row id = "anon-{h}-{hash}",    chevron id = "chevron-anon-{h}-{hash}"
        const row     = document.getElementById('books-' + key) || document.getElementById(key);
        const chevron = document.getElementById('chevron-' + key);
        if (!row) return;
        const hidden = row.classList.toggle('hidden');
        if (chevron) chevron.style.transform = hidden ? '' : 'rotate(180deg)';
    }

    // ── User-type filter ──
    function filterByUserType(h, type) {
        userTypeFilter[h] = type;

        // Update toggle button styles
        ['reg', 'anon', 'all'].forEach(t => {
            const btn = document.getElementById('ut-btn-' + h + '-' + t);
            if (!btn) return;
            if (t === type) {
                btn.classList.add('bg-white', 'dark:bg-white/10', 'text-slate-800', 'dark:text-white', 'shadow-sm');
                btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            } else {
                btn.classList.remove('bg-white', 'dark:bg-white/10', 'text-slate-800', 'dark:text-white', 'shadow-sm');
                btn.classList.add('text-slate-500', 'dark:text-slate-400');
            }
        });

        applyFilters(h);
    }

    // ── Book filter ──
    function filterByBook(h, contentId) {
        bookFilterState[h] = contentId;
        applyFilters(h);
    }

    // ── Core: apply both filters and sync KPI ──
    function applyFilters(h) {
        const uType    = userTypeFilter[h];
        const bookId   = bookFilterState[h];
        const rows     = document.querySelectorAll('.user-row-' + h);
        let visible = 0, totalChapters = 0, totalUniqueChapters = 0, bookSet = new Set();

        rows.forEach(tr => {
            const rowType  = tr.dataset.userType;  // 'registered' | 'anonymous'
            const ids      = (tr.dataset.bookIds || '').split(',').filter(Boolean);
            const expandId = tr.dataset.expandId;
            // Registered expand: id="books-{expandId}", Anonymous expand: id="{expandId}" directly
            const expand = expandId
                ? (document.getElementById('books-' + expandId) || document.getElementById(expandId))
                : null;

            const typeMatch = (uType === 'all')
                || (uType === 'reg'  && rowType === 'registered')
                || (uType === 'anon' && rowType === 'anonymous');
            const bookMatch = !bookId || ids.includes(String(bookId));
            const match     = typeMatch && bookMatch;

            tr.classList.toggle('hidden', !match);
            if (!match && expand) {
                expand.classList.add('hidden');
                // Reset chevron — id pattern: "chevron-{expandId}" works for both types
                const chevron = document.getElementById('chevron-' + expandId);
                if (chevron) chevron.style.transform = '';
            }

            if (match) {
                visible++;
                totalChapters       += parseInt(tr.dataset.chapters || 0, 10);
                totalUniqueChapters += parseInt(tr.dataset.uniqueChapters || 0, 10);
                ids.forEach(id => { if (id) bookSet.add(id); });
            }
        });

        // Update count badge
        const badge = document.getElementById('user-count-' + h);
        if (badge) badge.textContent = visible + ' user';

        // Sync KPI cards
        const kpiUsers          = document.getElementById('kpi-users-' + h);
        const kpiChapters       = document.getElementById('kpi-chapters-' + h);
        const kpiUniqueChapters = document.getElementById('kpi-unique-chapters-' + h);
        const kpiBooks          = document.getElementById('kpi-books-' + h);
        if (kpiUsers)           kpiUsers.textContent           = visible.toLocaleString();
        if (kpiChapters)        kpiChapters.textContent        = totalChapters.toLocaleString();
        if (kpiUniqueChapters)  kpiUniqueChapters.textContent  = totalUniqueChapters.toLocaleString() + ' unik';
        if (kpiBooks)           kpiBooks.textContent           = (bookId ? (bookSet.size > 0 ? 1 : 0) : bookSet.size).toLocaleString();
    }

    // ── Auto-refresh every 90 seconds ──
    let countdown = 90;
    setInterval(() => {
        countdown--;
        if (countdown <= 0) location.reload();
    }, 1000);
</script>
@endpush
