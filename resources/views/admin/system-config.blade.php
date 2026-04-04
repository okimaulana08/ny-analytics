@extends('layouts.admin')
@section('title', 'System Config')
@section('page-title', 'System Config')

@section('content')

<div class="mb-5 flex items-start justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Konfigurasi parameter bisnis aplikasi. Edit langsung di baris tabel.
        </p>
        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Halaman ini hanya untuk parameter non-sensitif. Jangan simpan API key, password, atau credential di sini.
        </p>
    </div>
    {{-- Seed button --}}
    <form method="POST" action="{{ route('admin.system-config.seed-defaults') }}">
        @csrf
        <button type="submit"
            class="h-9 px-4 inline-flex items-center gap-2 text-xs font-semibold rounded-xl border border-violet-200 dark:border-violet-500/30 text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors whitespace-nowrap"
            onclick="return confirm('Seed semua default config? Data yang sudah ada tidak akan diubah.')">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Seed Default Config
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-5 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-4 py-3 text-sm text-green-700 dark:text-green-400">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
    {{ $errors->first() }}
</div>
@endif

<div class="space-y-6">
    @if($configs->isEmpty())
    <div class="flat-card flex flex-col items-center justify-center py-20 text-slate-400">
        <svg class="w-12 h-12 mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada config tersedia.</p>
        <p class="text-xs mt-1">Klik tombol <strong class="text-violet-500">Seed Default Config</strong> di atas untuk mengisi data awal.</p>
    </div>
    @else
    @foreach($configs as $group => $items)
    <div class="flat-card overflow-hidden">

        {{-- Group header --}}
        <div class="px-5 py-3 bg-slate-50 dark:bg-white/[0.03] border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-2">
            <span class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-200">{{ $group }}</span>
            <span class="text-[11px] text-slate-400 font-mono">({{ $items->count() }} config)</span>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-56">Label</th>
                    <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-52">Key</th>
                    <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Value</th>
                    <th class="text-center px-4 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-20">Type</th>
                    <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
                @foreach($items as $cfg)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.015] transition-colors"
                    x-data="{
                        editing: false,
                        val: {{ json_encode($cfg->value) }},
                        saved: {{ json_encode($cfg->value) }},
                        cancel() { this.val = this.saved; this.editing = false; }
                    }">

                    {{-- Label --}}
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $cfg->label }}</span>
                    </td>

                    {{-- Key --}}
                    <td class="px-4 py-3">
                        <code class="text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded">{{ $cfg->key }}</code>
                    </td>

                    {{-- Value — display/edit --}}
                    <td class="px-4 py-3">
                        {{-- Display mode --}}
                        <div x-show="!editing" class="flex items-center gap-2">
                            <span class="font-mono text-sm font-semibold text-slate-800 dark:text-white" x-text="saved"></span>
                            <button @click="editing = true"
                                class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Edit mode --}}
                        <form x-show="editing" x-cloak
                            method="POST"
                            action="{{ route('admin.system-config.update', $cfg) }}"
                            class="flex items-center gap-2"
                            @submit.prevent="
                                $el.submit();
                                saved = val;
                                editing = false;
                            ">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="value" x-model="val"
                                class="w-36 h-7 px-2.5 text-xs font-mono rounded-lg border border-blue-300 dark:border-blue-500/50 bg-white dark:bg-white/5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition"
                                @keydown.escape="cancel()">
                            <button type="submit"
                                class="h-7 px-2.5 text-[11px] font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="cancel()"
                                class="h-7 px-2.5 text-[11px] font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                                Batal
                            </button>
                        </form>
                    </td>

                    {{-- Type badge --}}
                    <td class="px-4 py-3 text-center">
                        <span class="badge text-[10px]" style="{{ $cfg->typeBadgeStyle() }}">{{ $cfg->type }}</span>
                    </td>

                    {{-- Description --}}
                    <td class="px-5 py-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $cfg->description }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    @endif
</div>

@endsection
