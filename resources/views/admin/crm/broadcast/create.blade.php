@extends('layouts.admin')
@section('title', 'Broadcast Email')
@section('page-title', 'Broadcast Email')

@section('content')
<input type="hidden" id="_init_group_id" value="{{ old('group_id', '') }}">
<input type="hidden" id="_init_template_id" value="{{ old('template_id', '') }}">
<div class="max-w-3xl" x-data="broadcastForm()">

    {{-- Preview Modal --}}
    <div x-show="previewOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="previewOpen = false" style="display:none">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-white/[0.08]">
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Preview Email</p>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="previewLabel"></p>
                </div>
                <div class="flex items-center gap-2">
                    <div x-show="previewLoading" class="text-xs text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Memuat...
                    </div>
                    <button @click="previewOpen = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/[0.08] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-hidden">
                <iframe x-ref="previewFrame" class="w-full h-full border-0" style="min-height:500px"></iframe>
            </div>
        </div>
    </div>

    <div class="flat-card p-6">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Kirim Email ke Grup</h2>

        <form action="{{ route('admin.crm.broadcast.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nama Campaign --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Campaign <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Promo Ramadan 2026"
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Grup Penerima --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Grup Penerima <span class="text-red-500">*</span></label>
                <select name="group_id" x-model="groupId" @change="onGroupChange()" required
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('group_id') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                    <option value="">— Pilih Grup —</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }} ({{ $group->type === 'static' ? 'Static' : 'Dinamis' }})</option>
                    @endforeach
                </select>
                @error('group_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror

                {{-- Loading indicator --}}
                <div x-show="groupLoading" class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat daftar penerima...
                </div>
            </div>

            {{-- Recipient Management (shows when group selected) --}}
            <div x-show="groupId && !groupLoading" x-transition class="rounded-xl border border-slate-200 dark:border-white/[0.08]" style="display:none">
                {{-- Header --}}
                <div class="px-4 py-3 bg-slate-50 dark:bg-white/[0.03] border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Manajemen Penerima</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-slate-400">Total efektif:</span>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" x-text="effectiveCount"></span>
                        <span class="text-[11px] text-slate-400">penerima</span>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Group summary --}}
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>Dari grup:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="groupCount + ' penerima'"></span>
                        <span x-show="groupCount > 200" class="text-amber-500 text-[11px]">(menampilkan 200 pertama di UI)</span>
                    </div>

                    {{-- Exclude section --}}
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Exclude dari daftar</p>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input x-model="excludeQ" @input="filterExclude()" type="text" placeholder="Cari email atau nama untuk di-exclude..."
                                class="block w-full h-9 pl-9 pr-3.5 text-xs rounded-xl outline-none bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-red-500/40 focus:border-red-400">
                        </div>
                        {{-- Exclude search results --}}
                        <div x-show="excludeResults.length > 0" class="mt-1.5 max-h-36 overflow-y-auto rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-900 shadow-sm">
                            <template x-for="m in excludeResults" :key="m.email">
                                <button type="button" @click="addExclusion(m)"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors border-b border-slate-50 dark:border-white/[0.04] last:border-0">
                                    <div>
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200" x-text="m.email"></span>
                                        <span x-show="m.name" class="text-[11px] text-slate-400 ml-1.5" x-text="'— ' + m.name"></span>
                                    </div>
                                    <span class="text-[11px] text-red-500 font-medium ml-2 flex-shrink-0">Exclude</span>
                                </button>
                            </template>
                        </div>
                        {{-- Excluded list --}}
                        <div x-show="excluded.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="e in excluded" :key="e.email">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-full border border-red-200 dark:border-red-500/30">
                                    <span x-text="e.email"></span>
                                    <button type="button" @click="removeExclusion(e)" class="hover:text-red-800 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <input type="hidden" name="excluded_emails[]" :value="e.email">
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Add manual section --}}
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Tambah Penerima Manual</p>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input x-model="manualQ" @input="searchManual()" type="text" placeholder="Cari nama atau email user..."
                                class="block w-full h-9 pl-9 pr-3.5 text-xs rounded-xl outline-none bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-400">
                            {{-- Search results dropdown --}}
                            <div x-show="manualResults.length > 0" class="absolute z-50 top-full left-0 right-0 mt-1 max-h-40 overflow-y-auto rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-900 shadow-lg">
                                <template x-for="u in manualResults" :key="u.email">
                                    <button type="button" @click="addManualUser(u)"
                                        class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors border-b border-slate-50 dark:border-white/[0.04] last:border-0">
                                        <div>
                                            <span class="text-xs font-medium text-slate-700 dark:text-slate-200" x-text="u.email"></span>
                                            <span x-show="u.name" class="text-[11px] text-slate-400 ml-1.5" x-text="'— ' + u.name"></span>
                                        </div>
                                        <span class="text-[11px] text-emerald-600 font-medium ml-2 flex-shrink-0">+ Tambah</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        {{-- Extra list --}}
                        <div x-show="extras.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="e in extras" :key="e.email">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-full border border-emerald-200 dark:border-emerald-500/30">
                                    <span x-text="e.email + (e.name ? ' (' + e.name + ')' : '')"></span>
                                    <button type="button" @click="removeExtra(e)" class="hover:text-emerald-900 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <input type="hidden" name="extra_email[]" :value="e.email">
                                    <input type="hidden" name="extra_name[]" :value="e.name">
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Template Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Template Email <span class="text-red-500">*</span></label>
                <select name="template_id" x-model="templateId" @change="onTemplateChange()" required
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('template_id') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                    <option value="">— Pilih Template —</option>
                    @foreach($templates as $tmpl)
                        <option value="{{ $tmpl->id }}" data-subject="{{ $tmpl->subject }}" {{ old('template_id') == $tmpl->id ? 'selected' : '' }}>{{ $tmpl->name }}</option>
                    @endforeach
                </select>
                @error('template_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Preview Section (shows when template selected) --}}
            <div x-show="templateId" x-transition class="rounded-xl border border-violet-200 dark:border-violet-500/20 bg-violet-50/40 dark:bg-violet-500/5" style="display:none">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="text-xs font-semibold text-violet-700 dark:text-violet-300">Preview Email</span>
                    </div>
                </div>
                <div class="px-4 pb-4 border-t border-violet-100 dark:border-violet-500/10 pt-3">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input x-model="previewQ" @input="filterPreview()" type="text"
                                :placeholder="groupId ? 'Cari penerima dari grup...' : 'Masukkan email untuk preview...'"
                                class="block w-full h-9 pl-9 pr-3.5 text-xs rounded-xl outline-none bg-white dark:bg-white/[0.04] border border-violet-200 dark:border-violet-500/30 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500">
                            {{-- Search dropdown --}}
                            <div x-show="previewResults.length > 0" class="absolute z-50 top-full left-0 right-0 mt-1 max-h-40 overflow-y-auto rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-900 shadow-lg">
                                <template x-for="m in previewResults" :key="m.email">
                                    <button type="button" @click="selectPreviewUser(m)"
                                        class="w-full flex items-center px-3 py-2 text-left hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors border-b border-slate-50 dark:border-white/[0.04] last:border-0">
                                        <div>
                                            <span class="text-xs font-medium text-slate-700 dark:text-slate-200" x-text="m.email"></span>
                                            <span x-show="m.name" class="text-[11px] text-slate-400 ml-1.5" x-text="'— ' + m.name"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="openPreview()"
                            :disabled="!templateId"
                            class="h-9 px-4 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl transition-colors flex-shrink-0">
                            <span x-text="previewUser ? 'Preview untuk ' + (previewUser.name || previewUser.email) : 'Preview (data sampel)'"></span>
                        </button>
                    </div>
                    <p x-show="previewUser" class="mt-1.5 text-[11px] text-violet-500">
                        Preview menggunakan data aktual dari: <span class="font-semibold" x-text="previewUser?.name + ' (' + previewUser?.email + ')'"></span>
                        <button type="button" @click="previewUser = null; previewQ = ''" class="ml-1 text-slate-400 hover:text-slate-600 underline">Hapus</button>
                    </p>
                </div>
            </div>

            {{-- Subject --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Subject Email <span class="text-red-500">*</span></label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required placeholder="Subject email..."
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                @error('subject')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Jadwal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Jadwal Pengiriman</label>
                <div class="flex items-center gap-3 mb-2">
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="now" checked onchange="window.toggleSchedule(this)" class="accent-blue-600"> Kirim Sekarang
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="scheduled" onchange="window.toggleSchedule(this)" class="accent-blue-600"> Jadwalkan
                    </label>
                </div>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}"
                    class="hidden block w-full h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
                @error('scheduled_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Kirim Campaign
                </button>
                <a href="{{ route('admin.crm.campaigns.index') }}" class="h-10 px-5 flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('head-scripts')
<script>window._crmRoutes = window._crmRoutes || {}; window._crmRoutes.broadcastPreview = '{{ route('admin.crm.broadcast.preview') }}'; window._crmRoutes.searchUsers = '{{ route('admin.crm.broadcast.search-users') }}'; window._crmRoutes.previewForUser = '{{ route('admin.crm.broadcast.preview-for-user') }}';</script>
<script src="{{ asset('js/crm-broadcast-form.js') }}"></script>
@endpush
