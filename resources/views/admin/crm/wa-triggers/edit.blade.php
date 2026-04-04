@extends('layouts.admin')
@section('title', 'Edit Trigger WA')
@section('page-title', 'Edit Trigger WA')

@section('content')
<div x-data="waTriggerEditor()" class="space-y-6">

    <div>
        <a href="{{ route('admin.crm.wa-triggers.index') }}"
            class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar trigger
        </a>
    </div>

    @if(session('success'))
    <div class="rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-4 py-3 text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.crm.wa-triggers.update', $trigger) }}" method="POST">
        @csrf @method('PUT')

        {{-- Hidden delete list --}}
        <template x-for="id in deleteIds" :key="id">
            <input type="hidden" name="delete_template_ids[]" :value="id">
        </template>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Left: Konfigurasi --}}
            <div class="flat-card p-6">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-5">Konfigurasi Trigger</h2>
                @include('admin.crm.wa-triggers._form')
            </div>

            {{-- Right: Template Pesan --}}
            <div class="flat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Template Pesan</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Dipilih secara acak tiap pengiriman untuk menghindari blokir WA.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full"
                            :class="templates.length < 10 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400'"
                            x-text="templates.length + ' template'"></span>
                        <span x-show="templates.length < 10"
                            class="text-[11px] text-amber-600 dark:text-amber-400">
                            ⚠ Min. 10
                        </span>
                    </div>
                </div>

                {{-- Placeholder info --}}
                <div class="mb-4 p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.05]">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Placeholder tersedia:</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($trigger->availablePlaceholders() as $placeholder => $desc)
                            <span title="{{ $desc }}"
                                class="font-mono text-[10px] bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 px-1.5 py-0.5 rounded cursor-help text-slate-600 dark:text-slate-300">
                                {{ $placeholder }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Template list --}}
                <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                    <template x-for="(tpl, i) in templates" :key="tpl.key">
                        <div class="relative group">
                            <input type="hidden" :name="'templates[' + i + '][id]'" :value="tpl.id">
                            <div class="absolute left-3 top-2.5 text-[10px] font-bold text-slate-300 dark:text-slate-600 select-none" x-text="i + 1"></div>
                            <textarea
                                :name="'templates[' + i + '][body]'"
                                x-model="tpl.body"
                                rows="3"
                                class="w-full pl-7 pr-16 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition resize-none"
                                placeholder="Tulis pesan WA di sini..."></textarea>

                            {{-- Active toggle --}}
                            <div class="absolute right-8 top-2.5 flex items-center gap-1">
                                <input type="checkbox" :name="'templates[' + i + '][is_active]'" :id="'tpl_active_' + i"
                                    x-model="tpl.is_active" value="1"
                                    class="w-3.5 h-3.5 rounded text-green-600 border-slate-300 cursor-pointer focus:ring-green-500">
                            </div>

                            {{-- Delete button --}}
                            <button type="button" @click="removeTemplate(i)"
                                class="absolute right-2 top-2 w-5 h-5 flex items-center justify-center rounded text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors opacity-0 group-hover:opacity-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addTemplate()"
                    class="mt-3 w-full h-8 text-xs font-medium text-green-600 dark:text-green-400 border border-dashed border-green-300 dark:border-green-500/30 rounded-xl hover:bg-green-50 dark:hover:bg-green-500/10 transition-colors">
                    + Tambah Template
                </button>
            </div>

        </div>

        <div class="mt-4 flex items-center gap-3">
            <button type="submit"
                class="h-9 px-5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.crm.wa-triggers.index') }}"
                class="h-9 px-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors inline-flex items-center">
                Batal
            </a>
        </div>

    </form>
</div>

@push('scripts')
@php
    $templateData = $trigger->templates->map(fn($t) => [
        'id' => $t->id,
        'body' => $t->body,
        'is_active' => $t->is_active,
    ])->values();
@endphp
<script>
function waTriggerEditor() {
    const existing = @json($templateData);

    let keyCounter = existing.length;

    return {
        templates: existing.map((t, i) => ({ ...t, key: i })),
        deleteIds: [],

        addTemplate() {
            this.templates.push({ id: null, body: '', is_active: true, key: keyCounter++ });
        },

        removeTemplate(index) {
            const tpl = this.templates[index];
            if (tpl.id) {
                this.deleteIds.push(tpl.id);
            }
            this.templates.splice(index, 1);
        },
    };
}
</script>
@endpush
@endsection
