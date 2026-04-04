<div class="flex gap-6 items-start">

    {{-- ───────── LEFT COLUMN: Trigger settings ───────── --}}
    <div class="w-72 shrink-0 space-y-4">

        {{-- Nama --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Trigger <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $trigger->name ?? '') }}"
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
                placeholder="cth. Reminder 7 Hari Sebelum Expiry" required>
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Deskripsi</label>
            <input type="text" name="description" value="{{ old('description', $trigger->description ?? '') }}"
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
                placeholder="Opsional — keterangan singkat">
        </div>

        {{-- Jenis Trigger --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jenis Trigger <span class="text-red-400">*</span></label>
            <select name="trigger_type" id="trigger_type" required
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">— Pilih jenis —</option>
                <option value="pending_payment" {{ old('trigger_type', $trigger->trigger_type ?? '') === 'pending_payment' ? 'selected' : '' }}>
                    Pending Payment
                </option>
                <option value="expiry_reminder" {{ old('trigger_type', $trigger->trigger_type ?? '') === 'expiry_reminder' ? 'selected' : '' }}>
                    Reminder Expiry
                </option>
            </select>
            @error('trigger_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Condition: Pending Payment --}}
        <div id="cond_pending_payment" class="hidden">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kondisi <span class="text-red-400">*</span></label>
            <select name="condition" id="condition_pending"
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">— Pilih kondisi —</option>
                <option value="invoice_active" {{ old('condition', $trigger->condition ?? '') === 'invoice_active' ? 'selected' : '' }}>
                    Invoice Aktif — ada link pembayaran
                </option>
                <option value="invoice_expired" {{ old('condition', $trigger->condition ?? '') === 'invoice_expired' ? 'selected' : '' }}>
                    Invoice Expired — kirim link pilih paket
                </option>
            </select>
            <div class="mt-2">
                <div class="flex items-center gap-2">
                    <input type="number" name="delay_minutes" value="{{ old('delay_minutes', $trigger->conditions['delay_minutes'] ?? 30) }}"
                        min="1" max="10080"
                        class="w-20 h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
                    <span class="text-xs text-slate-500">menit setelah invoice pending dibuat</span>
                </div>
            </div>
            @error('condition') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Condition: Expiry Reminder --}}
        <div id="cond_expiry_reminder" class="hidden">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kondisi <span class="text-red-400">*</span></label>
            <select name="condition" id="condition_expiry"
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">— Pilih kondisi —</option>
                <option value="before_expiry" {{ old('condition', $trigger->condition ?? '') === 'before_expiry' ? 'selected' : '' }}>
                    Sebelum Berakhir
                </option>
                <option value="after_expiry" {{ old('condition', $trigger->condition ?? '') === 'after_expiry' ? 'selected' : '' }}>
                    Setelah Berakhir
                </option>
            </select>
            <div class="mt-2">
                <div class="flex items-center gap-2">
                    <input type="number" name="days_before" id="days_before_input"
                        value="{{ old('days_before', $trigger->conditions['days_before'] ?? $trigger->conditions['days_after'] ?? 7) }}"
                        min="1" max="30"
                        class="w-20 h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
                    <span id="expiry_days_label" class="text-xs text-slate-500">hari sebelum membership expiry</span>
                </div>
            </div>
            @error('days_before') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            @error('days_after') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Cooldown --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                Cooldown (hari) <span class="text-red-400">*</span>
            </label>
            <div class="flex items-center gap-2">
                <input type="number" name="cooldown_days" value="{{ old('cooldown_days', $trigger->cooldown_days ?? 14) }}"
                    min="1" max="365"
                    class="w-20 h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
                <span class="text-xs text-slate-500">hari min. antar email ke user yang sama</span>
            </div>
            @error('cooldown_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

    </div>{{-- end left column --}}

    {{-- ───────── RIGHT COLUMN: Email content editor ───────── --}}
    <div class="flex-1 min-w-0 space-y-3">

        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Konten Email</p>

        {{-- Subject --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                Subject <span class="text-red-400">*</span>
            </label>
            <input type="text" id="field_subject" name="subject"
                value="{{ old('subject', $trigger->subject ?? '') }}"
                required placeholder="Subject email..."
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition {{ $errors->has('subject') ? 'border-red-400' : '' }}">
            @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Preview Text --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Preview Text</label>
            <input type="text" id="field_preview_text" name="preview_text"
                value="{{ old('preview_text', $trigger->preview_text ?? '') }}"
                placeholder="Teks singkat di inbox email client (opsional)"
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        {{-- HTML Body / Editor --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">
                    Isi HTML Email <span class="text-red-400">*</span>
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
                </div>
            </div>

            {{-- Split pane --}}
            <div id="editor-pane-wrapper" class="flex gap-3 items-stretch">

                {{-- Left: CodeMirror --}}
                <div id="editor-pane" class="flex-1 min-w-0">
                    <textarea id="field_html_body" name="html_body" placeholder="<html>...</html>"
                        class="hidden {{ $errors->has('html_body') ? 'border-red-400' : '' }}">{{ old('html_body', $trigger->html_body ?? '') }}</textarea>
                    <div id="cm-editor-mount"
                        class="rounded-xl border {{ $errors->has('html_body') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }} overflow-hidden"
                        style="height: 480px;"></div>
                    @error('html_body') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Right: live preview iframe --}}
                <div id="preview-pane" class="hidden flex-1 min-w-0 rounded-xl border border-slate-200 dark:border-white/[0.08] overflow-hidden bg-white" style="height: 480px;">
                    <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 border-b border-slate-200 text-[11px] text-slate-400 shrink-0">
                        <span>Preview (data test)</span>
                        <span class="text-violet-500 font-medium">&#9999;&#65039; Klik elemen untuk edit langsung</span>
                    </div>
                    <div id="preview-loading" class="hidden items-center justify-center gap-2 text-xs text-slate-400" style="height: 448px;">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Memuat preview...
                    </div>
                    <iframe id="preview-iframe" class="w-full border-0" style="height: 448px;" sandbox="allow-same-origin"></iframe>
                </div>

            </div>
        </div>

    </div>{{-- end right column --}}

</div>

<script>
(function () {
    const typeSelect = document.getElementById('trigger_type');
    const condPanels = {
        pending_payment: document.getElementById('cond_pending_payment'),
        expiry_reminder: document.getElementById('cond_expiry_reminder'),
    };
    const conditionExpirySelect = document.getElementById('condition_expiry');
    const expiryDaysLabel = document.getElementById('expiry_days_label');
    const daysBeforeInput = document.getElementById('days_before_input');

    function updateConditions() {
        const val = typeSelect.value;
        Object.entries(condPanels).forEach(([key, el]) => {
            if (el) { el.classList.toggle('hidden', key !== val); }
        });
    }

    function updateExpiryCondition() {
        if (!conditionExpirySelect || !expiryDaysLabel || !daysBeforeInput) { return; }
        const isAfter = conditionExpirySelect.value === 'after_expiry';
        expiryDaysLabel.textContent = isAfter
            ? 'hari setelah expired (window tagihan)'
            : 'hari sebelum membership expiry';
        daysBeforeInput.name = isAfter ? 'days_after' : 'days_before';
    }

    typeSelect.addEventListener('change', updateConditions);
    if (conditionExpirySelect) {
        conditionExpirySelect.addEventListener('change', updateExpiryCondition);
    }

    updateConditions();
    updateExpiryCondition();
})();
</script>

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
#cm-editor-mount .CodeMirror-scroll { height: 480px; }
.cm-highlight-line { background: rgba(124, 58, 237, 0.18) !important; transition: background 0.4s; }
</style>
<script>
window._crmRoutes = window._crmRoutes || {};
window._crmRoutes.previewHtml     = '{{ route('admin.crm.templates.preview-html') }}';
window._crmRoutes.triggerDefaults = '{{ route('admin.crm.triggers.defaults') }}';
window._crmCurrentType     = 'custom';
window._crmTemplateSettings = {};

// Auto-fill subject/preview_text/html_body when type+condition is selected (create only)
document.addEventListener('DOMContentLoaded', function () {
    var isEditMode = {{ isset($trigger) ? 'true' : 'false' }};
    if (isEditMode) { return; }

    var typeSelect  = document.getElementById('trigger_type');
    var pendingCond = document.getElementById('condition_pending');
    var expiryCond  = document.getElementById('condition_expiry');

    function getActiveConditionSelect() {
        var type = typeSelect ? typeSelect.value : '';
        if (type === 'pending_payment') { return pendingCond; }
        if (type === 'expiry_reminder') { return expiryCond; }
        return null;
    }

    function maybeLoadDefaults() {
        var type      = typeSelect ? typeSelect.value : '';
        var condEl    = getActiveConditionSelect();
        var condition = condEl ? condEl.value : '';
        if (!type || !condition) { return; }

        var htmlBody = document.getElementById('field_html_body');
        if (htmlBody && htmlBody.value.trim()) { return; }

        fetch(window._crmRoutes.triggerDefaults + '?type=' + encodeURIComponent(type) + '&condition=' + encodeURIComponent(condition))
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) { return; }
                var subjectEl     = document.getElementById('field_subject');
                var previewTextEl = document.getElementById('field_preview_text');
                if (subjectEl && !subjectEl.value.trim())         { subjectEl.value = data.subject || ''; }
                if (previewTextEl && !previewTextEl.value.trim()) { previewTextEl.value = data.preview_text || ''; }
                if (data.html_body) {
                    if (htmlBody) { htmlBody.value = data.html_body; }
                    if (window.cmEditor) { window.cmEditor.setValue(data.html_body); }
                }
            });
    }

    if (typeSelect)  { typeSelect.addEventListener('change', maybeLoadDefaults); }
    if (pendingCond) { pendingCond.addEventListener('change', maybeLoadDefaults); }
    if (expiryCond)  { expiryCond.addEventListener('change', maybeLoadDefaults); }
});
</script>
<script src="{{ asset('js/crm-template-form.js') }}?v={{ filemtime(public_path('js/crm-template-form.js')) }}"></script>
@endpush
