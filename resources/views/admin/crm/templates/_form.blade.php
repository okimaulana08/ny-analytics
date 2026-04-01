@php
    $isEdit      = isset($template);
    $currentType = old('template_type', $isEdit ? $template->template_type : \App\Models\EmailTemplate::TYPE_CUSTOM);
    $settings    = old('template_settings', $isEdit ? ($template->template_settings ?? []) : []);
    $ET          = \App\Models\EmailTemplate::class;
@endphp

<div x-data="crmTemplateForm()" class="space-y-4">

    {{-- Type Selector (create) or Type Badge (edit, locked) --}}
    @if ($isEdit)
        <input type="hidden" name="template_type" value="{{ $template->template_type }}">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Tipe:</span>
            @php
                $badgeClass = match($template->template_type) {
                    $ET::TYPE_STORY_RECOMMENDATION => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
                    $ET::TYPE_PAYMENT_REMINDER     => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                    $ET::TYPE_PROMO                => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                    default                        => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
                };
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                {{ $ET::TYPE_LABELS[$template->template_type] ?? $template->template_type }}
            </span>
            <span class="text-xs text-slate-400">(tidak dapat diubah)</span>
        </div>
    @else
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Tipe Template <span class="text-red-500">*</span>
            </label>
            <input type="hidden" name="template_type" :value="templateType">
            <div class="grid grid-cols-2 gap-2">
                @foreach ($ET::TYPES as $type)
                    @php
                        $iconPath = match($type) {
                            $ET::TYPE_CUSTOM               => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                            $ET::TYPE_STORY_RECOMMENDATION => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                            $ET::TYPE_PAYMENT_REMINDER     => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                            $ET::TYPE_PROMO                => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                        };
                        $desc = match($type) {
                            $ET::TYPE_CUSTOM               => 'HTML bebas, bisa AI-generate',
                            $ET::TYPE_STORY_RECOMMENDATION => '3 cerita rekomendasi otomatis per penerima',
                            $ET::TYPE_PAYMENT_REMINDER     => 'Pengingat langganan berakhir',
                            $ET::TYPE_PROMO                => 'Banner image + headline + CTA promo',
                        };
                    @endphp
                    <button type="button" @click="templateType = '{{ $type }}'"
                        :class="templateType === '{{ $type }}'
                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 ring-2 ring-violet-500/30'
                            : 'border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] hover:border-slate-300 dark:hover:border-white/20'"
                        class="flex items-start gap-3 p-3 rounded-xl border text-left transition-all duration-150">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                        </svg>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-200 leading-tight">{{ $ET::TYPE_LABELS[$type] }}</div>
                            <div class="text-[10px] text-slate-400 leading-tight mt-0.5">{{ $desc }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- AI Generator Panel (custom only) --}}
    <div x-show="templateType === 'custom'" x-data="aiTemplateGenerator()"
        class="rounded-xl border border-violet-200 dark:border-violet-500/20 bg-violet-50/50 dark:bg-violet-500/5 overflow-hidden">
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

            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                    Tujuan Email <span class="text-red-500">*</span>
                </label>
                <textarea x-model="intent" rows="3" placeholder="Contoh: Email pengingat untuk user yang subscriptionnya akan berakhir 3 hari lagi. Ajak mereka renewal dengan pesan yang hangat."
                    class="block w-full px-3 py-2 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500"></textarea>
            </div>

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

    {{-- Info box: Story Recommendation --}}
    <div x-show="templateType === 'story_recommendation'"
        class="flex items-start gap-3 p-4 rounded-xl bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/20">
        <svg class="w-5 h-5 text-violet-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-violet-700 dark:text-violet-300">Template Otomatis — 3 Cerita Rekomendasi</p>
            <p class="text-xs text-violet-600/80 dark:text-violet-400/80 mt-0.5">
                Setiap penerima akan mendapatkan 3 cerita yang dipilih berdasarkan histori bacaan mereka.
                Tidak ada konten yang perlu diisi — semuanya digenerate otomatis saat pengiriman.
            </p>
        </div>
    </div>

    {{-- Info box: Payment Reminder --}}
    <div x-show="templateType === 'payment_reminder'"
        class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">Template Otomatis — Pengingat Pembayaran</p>
            <p class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-0.5">
                Konten email (nama, paket, tanggal berakhir, link invoice) diisi otomatis dari data subscription penerima.
                Tidak ada konten yang perlu diisi manual.
            </p>
        </div>
    </div>

    {{-- Promo Settings --}}
    <div x-show="templateType === 'promo'" class="rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-500/5 p-4 space-y-3">
        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Pengaturan Promo</p>

        <div class="grid grid-cols-1 gap-3">
            {{-- Banner URL --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                    URL Gambar Banner <span class="text-red-500" x-show="templateType === 'promo'">*</span>
                </label>
                <input type="text" name="template_settings[banner_url]"
                    x-model="promoSettings.banner_url" @input="onSettingsChange()"
                    value="{{ old('template_settings.banner_url', $settings['banner_url'] ?? '') }}"
                    placeholder="https://example.com/banner.jpg"
                    class="block w-full h-9 px-3 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 {{ $errors->has('template_settings.banner_url') ? 'border-red-400' : '' }}">
                @error('template_settings.banner_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Promo Headline --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                    Headline <span class="text-red-500" x-show="templateType === 'promo'">*</span>
                </label>
                <input type="text" name="template_settings[promo_headline]"
                    x-model="promoSettings.promo_headline" @input="onSettingsChange()"
                    value="{{ old('template_settings.promo_headline', $settings['promo_headline'] ?? '') }}"
                    placeholder="Diskon 50% Khusus Untukmu!"
                    class="block w-full h-9 px-3 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 {{ $errors->has('template_settings.promo_headline') ? 'border-red-400' : '' }}">
                @error('template_settings.promo_headline')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Promo Body --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi Promo</label>
                <textarea name="template_settings[promo_body]"
                    x-model="promoSettings.promo_body" @input="onSettingsChange()"
                    rows="3" placeholder="Ceritakan detail promonya di sini..."
                    class="block w-full px-3 py-2 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">{{ old('template_settings.promo_body', $settings['promo_body'] ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                {{-- CTA URL --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                        URL CTA <span class="text-red-500" x-show="templateType === 'promo'">*</span>
                    </label>
                    <input type="text" name="template_settings[cta_url]"
                        x-model="promoSettings.cta_url" @input="onSettingsChange()"
                        value="{{ old('template_settings.cta_url', $settings['cta_url'] ?? '') }}"
                        placeholder="https://novelya.id/promo/..."
                        class="block w-full h-9 px-3 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 {{ $errors->has('template_settings.cta_url') ? 'border-red-400' : '' }}">
                    @error('template_settings.cta_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- CTA Text --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                        Teks CTA <span class="text-red-500" x-show="templateType === 'promo'">*</span>
                    </label>
                    <input type="text" name="template_settings[cta_text]"
                        x-model="promoSettings.cta_text" @input="onSettingsChange()"
                        value="{{ old('template_settings.cta_text', $settings['cta_text'] ?? '') }}"
                        placeholder="Klaim Sekarang"
                        class="block w-full h-9 px-3 text-sm rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 {{ $errors->has('template_settings.cta_text') ? 'border-red-400' : '' }}">
                    @error('template_settings.cta_text')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Promo Code (optional) --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                    Kode Promo <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <input type="text" name="template_settings[promo_code]"
                    x-model="promoSettings.promo_code" @input="onSettingsChange()"
                    value="{{ old('template_settings.promo_code', $settings['promo_code'] ?? '') }}"
                    placeholder="HEMAT50"
                    class="block w-full h-9 px-3 text-sm font-mono rounded-xl outline-none transition-all duration-150 bg-white dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40">
            </div>
        </div>
    </div>

    {{-- HTML Body / Preview section --}}
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                <span x-show="templateType === 'custom'">Isi HTML Email <span class="text-red-500">*</span></span>
                <span x-show="templateType !== 'custom'">Preview HTML Template <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(read-only, digenerate otomatis)</span></span>
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
                <span x-show="templateType === 'custom'" class="text-[11px] text-slate-400">Merge tags: <code class="font-mono text-blue-500">@{{name}} @{{email}} @{{expiry_date}}</code></span>
            </div>
        </div>

        {{-- Split pane wrapper --}}
        <div id="editor-pane-wrapper" class="flex gap-3 items-stretch">

            {{-- Kiri: CodeMirror editor --}}
            <div id="editor-pane" class="flex-1 min-w-0">
                <textarea id="field_html_body" name="html_body" placeholder="<html>...</html>"
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
                    <span x-show="templateType === 'custom'" class="text-violet-500 font-medium">&#9999;&#65039; Klik elemen untuk edit langsung</span>
                    <span x-show="templateType !== 'custom'" class="text-slate-400">Preview otomatis</span>
                </div>
                <div id="preview-loading" class="hidden items-center justify-center gap-2 text-xs text-slate-400" style="height: 448px;">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat preview...
                </div>
                <iframe id="preview-iframe" class="w-full border-0" style="height: 448px;" sandbox="allow-same-origin"></iframe>
            </div>

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
.CodeMirror-readOnly { opacity: 0.75; }
</style>
<script>
window._crmRoutes = window._crmRoutes || {};
window._crmRoutes.aiGenerate  = '{{ route('admin.crm.templates.ai-generate') }}';
window._crmRoutes.previewHtml = '{{ route('admin.crm.templates.preview-html') }}';
window._crmTemplateType     = @json($currentType);
window._crmCurrentType      = @json($currentType);
window._crmTemplateSettings = {!! json_encode($settings ?: (object)[]) !!};

window.crmTemplateForm = function () {
    return {
        templateType: @json($currentType),
        promoSettings: {
            banner_url:     @json($settings['banner_url']     ?? ''),
            promo_headline: @json($settings['promo_headline'] ?? ''),
            promo_body:     @json($settings['promo_body']     ?? ''),
            cta_url:        @json($settings['cta_url']        ?? ''),
            cta_text:       @json($settings['cta_text']       ?? ''),
            promo_code:     @json($settings['promo_code']     ?? ''),
        },
        promoTimer: null,
        init: function () {
            this.$watch('templateType', function (v) {
                window._crmCurrentType = v;
                if (window._onTemplateTypeChange) { window._onTemplateTypeChange(v); }
            });
        },
        onSettingsChange: function () {
            window._crmTemplateSettings = this.promoSettings;
            clearTimeout(this.promoTimer);
            this.promoTimer = setTimeout(function () {
                if (window._refreshBuiltInPreview) { window._refreshBuiltInPreview(); }
            }, 600);
        },
    };
};
</script>
<script src="{{ asset('js/crm-template-form.js') }}?v={{ filemtime(public_path('js/crm-template-form.js')) }}"></script>
@endpush
