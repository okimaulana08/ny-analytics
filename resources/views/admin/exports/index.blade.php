@extends('layouts.admin')
@section('title', 'Export Data')
@section('page-title', 'Export Data')

@section('content')

<div class="mb-6">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        Download laporan dalam format Excel atau CSV. Pilih report yang dibutuhkan lalu klik tombol export.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

    {{-- Card: All Users --}}
    <div class="glass-card group relative overflow-hidden" x-data="exportCard()">
        {{-- Gradient accent top --}}
        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-t-2xl"></div>

        <div class="p-6">
            {{-- Icon + Badge --}}
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500/10 to-indigo-500/10 dark:from-blue-500/20 dark:to-indigo-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 rounded-full uppercase tracking-wide">
                    Users
                </span>
            </div>

            {{-- Title & Description --}}
            <h3 class="font-mono text-base font-bold text-slate-900 dark:text-white mb-1.5">All Users</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-5">
                Daftar lengkap semua user: Nama, Email, Phone, Status (Gratis / Member Aktif / Member Expired), dan Plan terakhir (Harian, Mingguan, Bulanan, Tahunan).
            </p>

            {{-- Export Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.exports.all-users', ['format' => 'excel']) }}"
                   @click="startDownload($event)"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold
                          bg-emerald-600 hover:bg-emerald-700 text-white
                          shadow-sm hover:shadow-md transition-all duration-200
                          active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </a>
                <a href="{{ route('admin.exports.all-users', ['format' => 'csv']) }}"
                   @click="startDownload($event)"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold
                          bg-slate-600 hover:bg-slate-700 dark:bg-slate-500 dark:hover:bg-slate-600 text-white
                          shadow-sm hover:shadow-md transition-all duration-200
                          active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    CSV
                </a>
            </div>

            {{-- Loading → Done overlay --}}
            <div x-show="loading || done" x-cloak
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm rounded-2xl flex items-center justify-center z-10">
                <div class="flex items-center gap-2 text-sm font-semibold" :class="done ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200'">
                    <template x-if="loading && !done">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </template>
                    <template x-if="done">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="done ? 'Berhasil diunduh!' : 'Mengunduh...'"></span>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function exportCard() {
        return {
            loading: false,
            done: false,
            startDownload(e) {
                e.preventDefault();
                if (this.loading) return;

                const url = e.currentTarget.href;
                this.loading = true;
                this.done = false;

                fetch(url, { credentials: 'same-origin' })
                    .then(res => {
                        const disposition = res.headers.get('Content-Disposition') || '';
                        const match = disposition.match(/filename="?([^"]+)"?/);
                        const filename = match ? match[1] : 'export.csv';
                        return res.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = filename;
                        a.click();
                        URL.revokeObjectURL(a.href);

                        this.loading = false;
                        this.done = true;
                        setTimeout(() => { this.done = false; }, 2000);
                    })
                    .catch(() => {
                        this.loading = false;
                        this.done = false;
                    });
            }
        };
    }
</script>
@endsection
