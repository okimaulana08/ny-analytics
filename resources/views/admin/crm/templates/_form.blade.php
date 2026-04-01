{{-- AI Generator Panel --}}
<div x-data="aiTemplateGenerator()" class="rounded-xl border border-violet-200 dark:border-violet-500/20 bg-violet-50/50 dark:bg-violet-500/5 overflow-hidden mb-1">
    {{-- Header --}}
    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-3 text-left">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="text-sm font-semibold text-violet-700 dark:text-violet-300">Generate dengan AI</span>
            <span class="text-[11px] text-violet-500 dark:text-violet-400 bg-violet-100 dark:bg-violet-500/20 px-2 py-0.5 rounded-full">Beta</span>
        </div>
        <svg class="w-4 h-4 text-violet-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Body --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 pb-4 border-t border-violet-200 dark:border-violet-500/20 pt-4">
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
            Deskripsikan tujuan email, tambahkan parameter dinamis yang diperlukan, lalu klik Generate.
            AI akan mengisi Nama, Subject, Preview Text, dan konten HTML secara otomatis.
        </p>

        {{-- Intent --}}
        <div class="mb-4">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                Tujuan Email <span class="text-red-500">*</span>
            </label>
            <textarea x-model="intent" rows="3" placeholder="Contoh: Email pengingat untuk user yang subscriptionnya akan berakhir 3 hari lagi. Ajak mereka renewal dengan pesan yang hangat."
                class="block w-full px-3 py-2 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500"></textarea>
        </div>

        {{-- Parameters --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Parameter Dinamis</label>
                <span class="text-[11px] text-slate-400">Nilai yang berubah per penerima</span>
            </div>

            {{-- Preset quick-add buttons --}}
            <div class="flex flex-wrap gap-1.5 mb-3">
                <template x-for="preset in presets" :key="preset.key">
                    <button type="button" @click="addPreset(preset)"
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-mono rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:border-violet-400 hover:text-violet-600 dark:hover:text-violet-300 transition-colors">
                        + <span x-text="preset.key"></span>
                    </button>
                </template>
            </div>

            {{-- Parameter list --}}
            <div class="space-y-2" x-show="params.length > 0">
                <template x-for="(param, idx) in params" :key="idx">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="param.key" placeholder="nama_param"
                            class="w-36 h-8 px-2.5 text-xs font-mono rounded-lg outline-none bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-violet-500/40">
                        <input type="text" x-model="param.description" placeholder="Deskripsi / contoh nilai"
                            class="flex-1 h-8 px-2.5 text-xs rounded-lg outline-none bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-violet-500/40">
                        <button type="button" @click="params.splice(idx, 1)"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <button type="button" @click="params.push({key: '', description: ''})"
                class="mt-2 inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Parameter
            </button>
        </div>

        {{-- Generate button & error --}}
        <div class="flex items-center gap-3">
            <button type="button" @click="generate()"
                :disabled="loading || !intent.trim()"
                class="h-9 px-5 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm shadow-violet-500/20 transition-all duration-150 inline-flex items-center gap-2">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span x-text="loading ? 'Membuat...' : 'Generate Template'"></span>
            </button>
            <p x-show="error" x-text="error" class="text-xs text-red-500"></p>
            <p x-show="success" class="text-xs text-emerald-600 dark:text-emerald-400">Template berhasil digenerate!</p>
        </div>
    </div>
</div>

{{-- Nama Template --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
        Nama Template <span class="text-red-500">*</span>
    </label>
    <input type="text" id="field_name" name="name" value="{{ old('name', $template->name ?? '') }}" required placeholder="Contoh: Welcome Email"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
    @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Subject --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
        Subject Default <span class="text-red-500">*</span>
    </label>
    <input type="text" id="field_subject" name="subject" value="{{ old('subject', $template->subject ?? '') }}" required placeholder="Subject email..."
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
    @error('subject')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Preview Text --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Preview Text</label>
    <input type="text" id="field_preview_text" name="preview_text" value="{{ old('preview_text', $template->preview_text ?? '') }}" placeholder="Teks singkat yang muncul di inbox email client (opsional)"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
</div>

{{-- HTML Body --}}
<div>
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Isi HTML Email <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-toggle-preview"
                class="h-7 px-2.5 inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Live Preview
            </button>
            <button type="button" onclick="openTemplatePreview()"
                class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-500/30 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Popup
            </button>
            <span class="text-[11px] text-slate-400">Merge tags: <code class="font-mono text-blue-500">@{{name}} @{{email}} @{{expiry_date}}</code></span>
        </div>
    </div>

    {{-- Split pane wrapper --}}
    <div id="editor-pane-wrapper" class="flex gap-3 items-stretch">

        {{-- Kiri: CodeMirror editor --}}
        <div id="editor-pane" class="flex-1 min-w-0">
            <textarea id="field_html_body" name="html_body" required placeholder="<html>...</html>"
                class="hidden {{ $errors->has('html_body') ? 'border-red-400' : '' }}">{{ old('html_body', $template->html_body ?? '') }}</textarea>
            <div id="cm-editor-mount"
                class="rounded-xl border {{ $errors->has('html_body') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }} overflow-hidden"
                style="height: 480px;"></div>
            @error('html_body')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Kanan: live preview iframe --}}
        <div id="preview-pane" class="hidden flex-1 min-w-0 rounded-xl border border-slate-200 dark:border-white/[0.08] overflow-hidden bg-white" style="height: 480px;">
            <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 border-b border-slate-200 text-[11px] text-slate-400 shrink-0">
                <span>Preview (data test)</span>
                <span class="text-violet-500 font-medium">✏ Klik elemen untuk edit langsung</span>
            </div>
            <div id="preview-loading" class="hidden items-center justify-center gap-2 text-xs text-slate-400" style="height: 448px;">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Memuat preview...
            </div>
            <iframe id="preview-iframe" class="w-full border-0" style="height: 448px;" sandbox="allow-same-origin"></iframe>
        </div>

    </div>
</div>

@push('head-scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/htmlmixed/htmlmixed.min.js"></script>
<style>
#cm-editor-mount .CodeMirror {
    height: 480px;
    font-family: 'Fira Code', 'Fira Mono', monospace;
    font-size: 13px;
    line-height: 1.6;
}
#cm-editor-mount .CodeMirror-scroll {
    height: 480px;
}
.cm-highlight-line { background: rgba(124, 58, 237, 0.18) !important; transition: background 0.4s; }
</style>
<script>window._crmRoutes = window._crmRoutes || {}; window._crmRoutes.aiGenerate = '{{ route('admin.crm.templates.ai-generate') }}'; window._crmRoutes.previewHtml = '{{ route('admin.crm.templates.preview-html') }}';</script>
<script src="{{ asset('js/crm-template-form.js') }}?v={{ filemtime(public_path('js/crm-template-form.js')) }}"></script>
@endpush
