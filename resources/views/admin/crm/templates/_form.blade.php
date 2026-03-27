{{-- Nama Template --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
        Nama Template <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required placeholder="Contoh: Welcome Email"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
    @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Subject --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
        Subject Default <span class="text-red-500">*</span>
    </label>
    <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" required placeholder="Subject email..."
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
    @error('subject')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Preview Text --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Preview Text</label>
    <input type="text" name="preview_text" value="{{ old('preview_text', $template->preview_text ?? '') }}" placeholder="Teks singkat yang muncul di inbox email client (opsional)"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
</div>

{{-- HTML Body --}}
<div>
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Isi HTML Email <span class="text-red-500">*</span>
        </label>
        <span class="text-[11px] text-slate-400">Merge tags: <code class="font-mono text-blue-500">@{{name}} @{{email}} @{{expiry_date}} @{{plan_name}}</code></span>
    </div>
    <textarea name="html_body" rows="16" required placeholder="<html>...</html>"
        class="block w-full px-3.5 py-2.5 text-sm font-mono rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('html_body') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">{{ old('html_body', $template->html_body ?? '') }}</textarea>
    @error('html_body')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
