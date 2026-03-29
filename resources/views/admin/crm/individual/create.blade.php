@extends('layouts.admin')
@section('title', 'Individual Email')
@section('page-title', 'Individual Email')

@section('content')
<div class="max-w-2xl" x-data="individualEmailForm()">
    <div class="flat-card p-6">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Kirim Email ke Individu</h2>

        <form action="{{ route('admin.crm.individual.store') }}" method="POST" class="space-y-4" @submit="onSubmit">
            @csrf

            {{-- Penerima (User Search) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Email Penerima <span class="text-red-500">*</span>
                </label>

                {{-- Hidden fields submitted with form --}}
                <input type="hidden" name="recipient_email" :value="selectedEmail">
                <input type="hidden" name="recipient_name"  :value="selectedName">

                {{-- Selected user badge --}}
                <div x-show="selectedEmail" class="flex items-center gap-2 mb-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg text-sm">
                        <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-blue-700 dark:text-blue-300 font-medium" x-text="selectedName || selectedEmail"></span>
                        <span class="text-blue-400 dark:text-blue-500 text-xs" x-show="selectedName" x-text="'— ' + selectedEmail"></span>
                    </div>
                    <button type="button" @click="clearSelection()"
                        class="text-xs text-slate-400 hover:text-red-500 transition-colors">Ganti</button>
                </div>

                {{-- Search input --}}
                <div x-show="!selectedEmail" class="relative">
                    <input type="text" x-model="q" @input.debounce.300ms="search()" @keydown.escape="results = []"
                        placeholder="Cari nama atau email user..."
                        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">

                    {{-- Dropdown --}}
                    <div x-show="results.length > 0" @click.outside="results = []"
                        class="absolute z-20 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/[0.10] rounded-xl shadow-lg overflow-hidden">
                        <template x-for="u in results" :key="u.email">
                            <button type="button" @click="selectUser(u)"
                                class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/[0.04] text-left transition-colors">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <div>
                                    <div class="text-sm text-slate-800 dark:text-slate-100 font-medium" x-text="u.name || '(tanpa nama)'"></div>
                                    <div class="text-xs text-slate-400" x-text="u.email"></div>
                                </div>
                            </button>
                        </template>
                        <div x-show="searched && results.length === 0" class="px-4 py-3 text-xs text-slate-400">Tidak ada user ditemukan.</div>
                    </div>
                </div>
                @error('recipient_email')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Template --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Template Email <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <select name="template_id" id="template_id" x-model="templateId" @change="onTemplateChange()" required
                        class="flex-1 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('template_id') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                        <option value="">— Pilih Template —</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" data-subject="{{ $tmpl->subject }}" {{ old('template_id') == $tmpl->id ? 'selected' : '' }}>
                                {{ $tmpl->name }}
                            </option>
                        @endforeach
                    </select>
                    <a id="preview-template-link" :href="previewUrl" target="_blank" :class="templateId ? '' : 'pointer-events-none opacity-40'"
                        class="h-10 px-3 inline-flex items-center text-xs text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                        Preview
                    </a>
                </div>
                @error('template_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Subject --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Subject Email <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required placeholder="Subject email..."
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                @error('subject')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Jadwal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Jadwal Pengiriman
                </label>
                <div class="flex items-center gap-3 mb-2">
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="now" checked onchange="toggleSchedule(this)" class="accent-blue-600"> Kirim Sekarang
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="scheduled" onchange="toggleSchedule(this)" class="accent-blue-600"> Jadwalkan
                    </label>
                </div>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}"
                    class="hidden block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
                @error('scheduled_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Kirim Email
                </button>
                <a href="{{ route('admin.crm.campaigns.index') }}"
                    class="h-10 px-5 flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSchedule(radio) {
    const el = document.getElementById('scheduled_at');
    if (radio.value === 'scheduled') {
        el.classList.remove('hidden');
        el.required = true;
    } else {
        el.classList.add('hidden');
        el.required = false;
        el.value = '';
    }
}

function individualEmailForm() {
    return {
        q: '',
        results: [],
        searched: false,
        selectedEmail: '{{ old('recipient_email', '') }}',
        selectedName: '{{ old('recipient_name', '') }}',
        templateId: '{{ old('template_id', '') }}',

        get previewUrl() {
            if (!this.templateId) return '#';
            const params = new URLSearchParams({
                name:  this.selectedName  || 'Pengguna Demo',
                email: this.selectedEmail || 'demo@novelya.id',
            });
            return '/admin/crm/templates/' + this.templateId + '/preview?' + params.toString();
        },

        async search() {
            const q = this.q.trim();
            if (q.length < 2) { this.results = []; this.searched = false; return; }
            const url = new URL('{{ route('admin.crm.broadcast.search-users') }}', location.origin);
            url.searchParams.set('q', q);
            const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            this.results = await res.json();
            this.searched = true;
        },

        selectUser(u) {
            this.selectedEmail = u.email;
            this.selectedName  = u.name || '';
            this.q = '';
            this.results = [];
        },

        clearSelection() {
            this.selectedEmail = '';
            this.selectedName  = '';
            this.q = '';
            this.results = [];
            this.searched = false;
        },

        onTemplateChange() {
            const sel = document.getElementById('template_id');
            const subject = sel.options[sel.selectedIndex]?.dataset?.subject ?? '';
            const subjectInput = document.getElementById('subject');
            if (subject && !subjectInput.value) {
                subjectInput.value = subject;
            } else if (subject && subjectInput.value && confirm('Timpa subject dengan default dari template?')) {
                subjectInput.value = subject;
            }
        },

        onSubmit(e) {
            if (!this.selectedEmail) {
                e.preventDefault();
                alert('Pilih email penerima terlebih dahulu.');
            }
        },
    };
}
</script>
@endpush
