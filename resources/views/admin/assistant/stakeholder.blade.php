@extends('layouts.admin')
@section('title', 'Stakeholder Assistant')
@section('page-title', 'Stakeholder — Business Intelligence')

@section('content')

{{-- Health Score --}}
<div class="glass-card p-5 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Business Health Score</p>
            <div class="flex items-end gap-3">
                <span class="font-mono text-4xl font-bold
                    {{ $health['overall'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($health['overall'] >= 45 ? 'text-amber-500' : 'text-red-500') }}">
                    {{ $health['overall'] }}
                </span>
                <span class="text-slate-400 text-sm mb-1">/ 100</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach([
                'pricing'   => ['label' => 'Pricing',   'color' => 'blue'],
                'content'   => ['label' => 'Konten',    'color' => 'teal'],
                'retention' => ['label' => 'Retention', 'color' => 'violet'],
                'growth'    => ['label' => 'Growth',    'color' => 'orange'],
            ] as $key => $cfg)
            @php $score = $health['breakdown'][$key]; @endphp
            <div class="text-center px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.04] min-w-[80px]">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ $cfg['label'] }}</p>
                <p class="font-mono text-lg font-bold
                    {{ $score >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($score >= 45 ? 'text-amber-500' : 'text-red-500') }}">
                    {{ $score }}
                </p>
                {{-- Mini progress bar --}}
                <div class="mt-1.5 h-1 bg-slate-200 dark:bg-white/10 rounded-full overflow-hidden w-16 mx-auto">
                    <div class="h-full rounded-full
                        {{ $score >= 70 ? 'bg-emerald-500' : ($score >= 45 ? 'bg-amber-400' : 'bg-red-500') }}"
                        style="width: {{ $score }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Key Metrics Snapshot --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
    @php
        $convColor = $metrics['conversion_rate'] >= 3.5 ? 'text-emerald-600 dark:text-emerald-400' : ($metrics['conversion_rate'] >= 2 ? 'text-amber-500' : 'text-red-500');
        $churnColor = $metrics['churn_rate'] <= 15 ? 'text-emerald-600 dark:text-emerald-400' : ($metrics['churn_rate'] <= 30 ? 'text-amber-500' : 'text-red-500');
        $revGrowth = $metrics['prev_month_rev'] > 0 ? round(($metrics['revenue_30d'] - $metrics['prev_month_rev']) / $metrics['prev_month_rev'] * 100, 1) : 0;
    @endphp

    <div class="glass-card p-4">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Conv. Rate</p>
        <p class="font-mono text-2xl font-bold {{ $convColor }}">{{ $metrics['conversion_rate'] }}%</p>
        <p class="text-[10px] text-slate-400 mt-1">Target: &gt;3.5%</p>
    </div>

    <div class="glass-card p-4">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ARPU</p>
        <p class="font-mono text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($metrics['arpu'], 0, ',', '.') }}</p>
        <p class="text-[10px] text-slate-400 mt-1">per paid user</p>
    </div>

    <div class="glass-card p-4">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Churn Rate</p>
        <p class="font-mono text-2xl font-bold {{ $churnColor }}">{{ $metrics['churn_rate'] }}%</p>
        <p class="text-[10px] text-slate-400 mt-1">30 hari</p>
    </div>

    <div class="glass-card p-4">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Avg Depth</p>
        <p class="font-mono text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $metrics['avg_chapters_per_view'] }}</p>
        <p class="text-[10px] text-slate-400 mt-1">chapter/view</p>
    </div>

    <div class="glass-card p-4">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Rev. Growth</p>
        <p class="font-mono text-2xl font-bold {{ $revGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
            {{ $revGrowth >= 0 ? '+' : '' }}{{ $revGrowth }}%
        </p>
        <p class="text-[10px] text-slate-400 mt-1">vs bulan lalu</p>
    </div>
</div>

{{-- Insights --}}
<div class="mb-5">
    @php
        $severityConfig = [
            'urgent'   => ['label' => 'URGENT',   'dot' => 'bg-red-500',   'badge' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',   'border' => 'border-red-200 dark:border-red-500/20',   'card' => 'bg-red-50/50 dark:bg-red-500/[0.05]'],
            'warning'  => ['label' => 'WARNING',  'dot' => 'bg-amber-500', 'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400', 'border' => 'border-amber-200 dark:border-amber-500/20', 'card' => 'bg-amber-50/50 dark:bg-amber-500/[0.04]'],
            'info'     => ['label' => 'INFO',     'dot' => 'bg-blue-500',  'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',   'border' => 'border-blue-200 dark:border-blue-500/20',   'card' => 'bg-blue-50/50 dark:bg-blue-500/[0.04]'],
            'positive' => ['label' => 'POSITIF',  'dot' => 'bg-emerald-500','badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400','border' => 'border-emerald-200 dark:border-emerald-500/20','card' => 'bg-emerald-50/50 dark:bg-emerald-500/[0.04]'],
        ];
        $catLabels = ['pricing' => 'Pricing', 'content' => 'Konten', 'retention' => 'Retention', 'growth' => 'Growth'];
    @endphp

    @foreach(['urgent', 'warning', 'info', 'positive'] as $sev)
    @if(!empty($grouped[$sev]))
    @php $cfg = $severityConfig[$sev]; @endphp
    <div class="mb-4">
        <div class="flex items-center gap-2 mb-2.5 px-1">
            <span class="w-2 h-2 rounded-full {{ $cfg['dot'] }} flex-shrink-0"></span>
            <span class="text-[11px] font-bold {{ $cfg['badge'] }} px-2 py-0.5 rounded-full">{{ $cfg['label'] }}</span>
            <span class="text-[11px] text-slate-400">({{ count($grouped[$sev]) }})</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($grouped[$sev] as $ins)
            <div class="rounded-2xl border {{ $cfg['border'] }} {{ $cfg['card'] }} p-4">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <p class="text-xs font-bold text-slate-800 dark:text-white leading-snug">{{ $ins['title'] }}</p>
                    <span class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded-full whitespace-nowrap flex-shrink-0">
                        {{ $catLabels[$ins['category']] ?? $ins['category'] }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mb-3">{{ $ins['desc'] }}</p>
                <div class="flex items-start gap-2 bg-white/60 dark:bg-white/[0.04] rounded-xl p-2.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <p class="text-[11px] text-slate-700 dark:text-slate-200 leading-relaxed">{{ $ins['suggestion'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    @if(empty($insights))
    <div class="glass-card p-10 text-center text-slate-400 text-sm">Tidak ada insight yang terdeteksi saat ini.</div>
    @endif
</div>

{{-- AI Analysis --}}
<div class="mb-5 rounded-2xl overflow-hidden border border-violet-200/70 dark:border-violet-500/20 shadow-sm">

    {{-- Card header with gradient --}}
    <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3"
         style="background: linear-gradient(135deg, rgba(139,92,246,0.09) 0%, rgba(255,255,255,0.6) 70%);
                backdrop-filter: blur(12px); border-bottom: 1px solid rgba(139,92,246,0.12);">
        <div class="dark:hidden" style="background: inherit; position: absolute; inset: 0; pointer-events: none;"></div>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                <svg class="w-4.5 h-4.5 text-white w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">AI Narrative Analysis</h2>
                    <span class="text-[9px] font-bold tracking-wider text-violet-600 dark:text-violet-400 bg-violet-100 dark:bg-violet-500/15 px-1.5 py-0.5 rounded-full uppercase">Claude Haiku</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Analisis kontekstual & rekomendasi aksi berdasarkan data real-time</p>
            </div>
        </div>

        @if($hasAiKey)
        <button id="ai-generate-btn" onclick="generateAiInsight()"
            class="flex items-center gap-2 px-4 py-2 text-white text-xs font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95"
            style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
            <svg id="ai-btn-icon" class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span id="ai-btn-text">Generate AI Analysis</span>
        </button>
        @else
        <div class="flex items-center gap-2 text-[11px] text-slate-400 bg-slate-100 dark:bg-white/[0.04] px-3 py-2 rounded-xl">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Set <code class="font-mono text-violet-500 mx-0.5">ANTHROPIC_API_KEY</code> di .env untuk mengaktifkan
        </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-5 bg-white/70 dark:bg-white/[0.02]" style="backdrop-filter: blur(8px);">

        {{-- Loading skeleton --}}
        <div id="ai-loading" class="hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-6 h-6 rounded-full bg-violet-100 dark:bg-violet-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-violet-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">Claude sedang menganalisis data bisnis Novelya...</span>
                <span class="flex gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-bounce" style="animation-delay:300ms"></span>
                </span>
            </div>
            <div class="space-y-3">
                <div class="h-3 bg-slate-100 dark:bg-white/[0.05] rounded-full w-3/4 animate-pulse"></div>
                <div class="h-3 bg-slate-100 dark:bg-white/[0.05] rounded-full w-full animate-pulse"></div>
                <div class="h-3 bg-slate-100 dark:bg-white/[0.05] rounded-full w-5/6 animate-pulse"></div>
                <div class="h-3 bg-slate-100 dark:bg-white/[0.05] rounded-full w-2/3 animate-pulse mt-5"></div>
                <div class="h-3 bg-slate-100 dark:bg-white/[0.05] rounded-full w-full animate-pulse"></div>
            </div>
        </div>

        {{-- Error --}}
        <div id="ai-error" class="hidden">
            <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl p-4">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-0.5">Gagal Generate Analisis</p>
                    <p id="ai-error-msg" class="text-xs text-red-600 dark:text-red-300"></p>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        @if(!$aiNarrative && $hasAiKey)
        <div id="ai-empty" class="py-10 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                 style="background: linear-gradient(135deg, rgba(139,92,246,0.12), rgba(168,85,247,0.06));">
                <svg class="w-7 h-7 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Belum Ada Analisis AI</p>
            <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
                Klik <strong class="text-violet-500">Generate AI Analysis</strong> untuk mendapatkan ringkasan kondisi bisnis dan 3 prioritas aksi dari Claude AI
            </p>
        </div>
        @endif

        {{-- AI Narrative Result --}}
        <div id="ai-result-wrap" class="{{ $aiNarrative ? '' : 'hidden' }}">

            {{-- Timestamp badge --}}
            <div id="ai-meta" class="flex items-center gap-2 mb-4">
                @if($aiCachedAt)
                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/20 px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $aiCachedAt }} · cache 1 jam
                </span>
                @endif
            </div>

            {{-- Sections container --}}
            <div id="ai-narrative" class="space-y-4">
                @if($aiNarrative)
                    {!! renderAiNarrative($aiNarrative) !!}
                @endif
            </div>
        </div>

    </div>
</div>

@php
/* ─── PHP Markdown Renderer ──────────────────────────────────────────────────
   Handles: ## sections, ### h3, 1. numbered, - bullets, **bold**, *italic*,
   `code`, > blockquote, paragraphs, and the colored section-card layout.
   ─────────────────────────────────────────────────────────────────────────── */
function renderAiNarrative(string $text): string
{
    $sectionMap = [
        'ringkasan'  => ['icon'=>'📊','label'=>'Ringkasan Kondisi','border'=>'border-blue-200 dark:border-blue-500/20',  'bg'=>'bg-blue-50/70 dark:bg-blue-500/[0.06]',  'head'=>'text-blue-700 dark:text-blue-300',  'numBg'=>'#1d4ed8'],
        'kondisi'    => ['icon'=>'📊','label'=>'Ringkasan Kondisi','border'=>'border-blue-200 dark:border-blue-500/20',  'bg'=>'bg-blue-50/70 dark:bg-blue-500/[0.06]',  'head'=>'text-blue-700 dark:text-blue-300',  'numBg'=>'#1d4ed8'],
        'prioritas'  => ['icon'=>'🎯','label'=>'Prioritas Aksi',  'border'=>'border-violet-200 dark:border-violet-500/20','bg'=>'bg-violet-50/70 dark:bg-violet-500/[0.06]','head'=>'text-violet-700 dark:text-violet-300','numBg'=>'#7c3aed'],
        'aksi'       => ['icon'=>'🎯','label'=>'Prioritas Aksi',  'border'=>'border-violet-200 dark:border-violet-500/20','bg'=>'bg-violet-50/70 dark:bg-violet-500/[0.06]','head'=>'text-violet-700 dark:text-violet-300','numBg'=>'#7c3aed'],
        'peringatan' => ['icon'=>'⚠️','label'=>'Peringatan Risiko','border'=>'border-amber-200 dark:border-amber-500/20', 'bg'=>'bg-amber-50/70 dark:bg-amber-500/[0.06]', 'head'=>'text-amber-700 dark:text-amber-300', 'numBg'=>'#b45309'],
        'risiko'     => ['icon'=>'⚠️','label'=>'Peringatan Risiko','border'=>'border-amber-200 dark:border-amber-500/20', 'bg'=>'bg-amber-50/70 dark:bg-amber-500/[0.06]', 'head'=>'text-amber-700 dark:text-amber-300', 'numBg'=>'#b45309'],
    ];
    $fallback = ['icon'=>'💡','label'=>'','border'=>'border-slate-200 dark:border-slate-700','bg'=>'bg-slate-50/70 dark:bg-white/[0.03]','head'=>'text-slate-600 dark:text-slate-300','numBg'=>'#475569'];

    /* Inline formatting: bold, italic, code, preserve HTML-escaped input */
    $inline = function(string $s): string {
        $s = htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $s = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong class="font-bold text-slate-900 dark:text-white"><em>$1</em></strong>', $s);
        $s = preg_replace('/\*\*(.+?)\*\*/',     '<strong class="font-semibold text-slate-900 dark:text-white">$1</strong>', $s);
        $s = preg_replace('/\*(.+?)\*/',          '<em class="italic text-slate-500 dark:text-slate-400">$1</em>', $s);
        $s = preg_replace('/`(.+?)`/',            '<code class="font-mono text-[11px] bg-slate-100 dark:bg-white/10 text-violet-600 dark:text-violet-300 px-1.5 py-0.5 rounded">$1</code>', $s);
        return $s;
    };

    /* Render a block of text (body of a section) into styled HTML */
    $renderBody = function(string $body, array $cfg) use ($inline): string {
        $lines  = explode("\n", $body);
        $out    = '';
        $i      = 0;
        $n      = count($lines);

        while ($i < $n) {
            $line = rtrim($lines[$i]);

            // Skip blank
            if (trim($line) === '') { $i++; continue; }

            // ### sub-heading
            if (preg_match('/^###\s+(.+)$/', $line, $m)) {
                $out .= '<p class="font-mono text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-3 mb-1">' . $inline($m[1]) . '</p>';
                $i++; continue;
            }

            // > blockquote
            if (preg_match('/^>\s*(.*)$/', $line, $m)) {
                $qLines = [];
                while ($i < $n && preg_match('/^>\s*(.*)$/', rtrim($lines[$i]), $qm)) {
                    $qLines[] = $qm[1];
                    $i++;
                }
                $qText = $inline(implode(' ', $qLines));
                $out .= '<blockquote class="border-l-2 border-slate-300 dark:border-slate-600 pl-3 my-2 text-[12px] italic text-slate-500 dark:text-slate-400">' . $qText . '</blockquote>';
                continue;
            }

            // Numbered list item: collect multi-line item text
            if (preg_match('/^(\d+)\.\s+(.*)$/', $line, $m)) {
                // Collect all consecutive numbered items
                $items = [];
                while ($i < $n) {
                    $cur = rtrim($lines[$i]);
                    if (preg_match('/^(\d+)\.\s+(.*)$/', $cur, $nm)) {
                        $num  = (int)$nm[1];
                        $text = $nm[2];
                        // Continuation lines (indented or plain non-list)
                        $i++;
                        while ($i < $n && trim($lines[$i]) !== '' &&
                               !preg_match('/^(\d+)\.\s+/', $lines[$i]) &&
                               !preg_match('/^[-*]\s+/', $lines[$i]) &&
                               !preg_match('/^>\s/', $lines[$i])) {
                            $text .= ' ' . trim($lines[$i]);
                            $i++;
                        }
                        $items[] = [$num, trim($text)];
                    } else {
                        break;
                    }
                }
                $out .= '<div class="space-y-3 my-1">';
                foreach ($items as [$num, $text]) {
                    // Try to split "**Title**: description"
                    if (preg_match('/^\*\*(.+?)\*\*[:\s]+(.+)$/s', $text, $tm)) {
                        $title = $inline($tm[1]);
                        $desc  = $inline(trim($tm[2]));
                        $out .= '<div class="flex gap-3 items-start">'
                            . '<span class="flex-shrink-0 w-6 h-6 rounded-lg text-white font-mono font-bold text-[10px] flex items-center justify-center mt-0.5" style="background:' . $cfg['numBg'] . '">' . $num . '</span>'
                            . '<div><p class="font-semibold text-slate-800 dark:text-white text-[12px] leading-snug mb-0.5">' . $title . '</p>'
                            . '<p class="text-[12px] text-slate-600 dark:text-slate-300 leading-relaxed">' . $desc . '</p></div>'
                            . '</div>';
                    } else {
                        $out .= '<div class="flex gap-3 items-start">'
                            . '<span class="flex-shrink-0 w-6 h-6 rounded-lg text-white font-mono font-bold text-[10px] flex items-center justify-center mt-0.5" style="background:' . $cfg['numBg'] . '">' . $num . '</span>'
                            . '<p class="text-[12px] text-slate-700 dark:text-slate-200 leading-relaxed pt-0.5">' . $inline($text) . '</p>'
                            . '</div>';
                    }
                }
                $out .= '</div>';
                continue;
            }

            // Bullet list: - or *
            if (preg_match('/^[-*]\s+(.*)$/', $line, $m)) {
                $bullets = [];
                while ($i < $n) {
                    $cur = rtrim($lines[$i]);
                    if (preg_match('/^[-*]\s+(.*)$/', $cur, $bm)) {
                        $bullets[] = $bm[1];
                        $i++;
                    } else { break; }
                }
                $out .= '<ul class="space-y-1.5 my-2 ml-1">';
                foreach ($bullets as $b) {
                    $out .= '<li class="flex gap-2 items-start text-[12px] text-slate-700 dark:text-slate-200">'
                        . '<span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 mt-1.5"></span>'
                        . '<span class="leading-relaxed">' . $inline($b) . '</span>'
                        . '</li>';
                }
                $out .= '</ul>';
                continue;
            }

            // Paragraph: collect lines until blank/list/header
            $para = [];
            while ($i < $n) {
                $cur = rtrim($lines[$i]);
                if (trim($cur) === '' ||
                    preg_match('/^(#{1,3}|\d+\.|[-*]|>)\s/', $cur)) { break; }
                $para[] = $cur;
                $i++;
            }
            if ($para) {
                $out .= '<p class="text-[13px] text-slate-700 dark:text-slate-200 leading-relaxed mb-2">'
                    . $inline(implode(' ', $para)) . '</p>';
            }
        }
        return $out;
    };

    // Split text into ## sections
    $sections = preg_split('/^##\s+/m', $text, -1, PREG_SPLIT_NO_EMPTY);

    // No sections — render as single styled block
    if (count($sections) <= 1 && !preg_match('/^##\s+/m', $text)) {
        $body = $renderBody(trim($text), $fallback);
        return '<div class="text-[13px] leading-relaxed space-y-2">' . $body . '</div>';
    }

    $html = '<div class="space-y-4">';
    foreach ($sections as $section) {
        $nl      = strpos($section, "\n");
        $heading = $nl !== false ? trim(substr($section, 0, $nl)) : trim($section);
        $body    = $nl !== false ? trim(substr($section, $nl + 1)) : '';

        // Detect section config
        $cfg        = array_merge($fallback, ['label' => $heading]);
        $lower      = mb_strtolower($heading);
        foreach ($sectionMap as $key => $c) {
            if (str_contains($lower, $key)) { $cfg = $c; break; }
        }

        $bodyHtml = $body ? $renderBody($body, $cfg) : '';

        $html .= '<div class="rounded-2xl border ' . $cfg['border'] . ' ' . $cfg['bg'] . ' overflow-hidden">';
        // Section header bar
        $html .= '<div class="flex items-center gap-2.5 px-4 py-2.5 border-b ' . $cfg['border'] . ' bg-white/40 dark:bg-white/[0.03]">';
        $html .= '<span class="text-sm leading-none">' . $cfg['icon'] . '</span>';
        $html .= '<span class="font-mono text-[11px] font-bold ' . $cfg['head'] . ' uppercase tracking-wider">' . htmlspecialchars($cfg['label'] ?: $heading, ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '</div>';
        // Body
        $html .= '<div class="px-4 py-4">' . ($bodyHtml ?: '<p class="text-[12px] text-slate-400 italic">—</p>') . '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
@endphp

{{-- Additional Metrics Detail --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    <div class="glass-card p-5">
        <h3 class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 mb-3">Pricing Breakdown</h3>
        <div class="space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Total User</span>
                <span class="font-mono font-semibold text-slate-800 dark:text-white">{{ number_format($metrics['total_users']) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Paying Users</span>
                <span class="font-mono font-semibold text-slate-800 dark:text-white">{{ number_format($metrics['paid_users']) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Revenue 30h</span>
                <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($metrics['revenue_30d'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Plan Terlaris</span>
                <span class="font-mono text-slate-700 dark:text-slate-200">{{ $metrics['top_plan'] }} ({{ $metrics['top_plan_pct'] }}%)</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Share Plan Harian</span>
                <span class="font-mono {{ $metrics['daily_plan_pct'] > 55 ? 'text-amber-500' : 'text-slate-700 dark:text-slate-200' }}">{{ $metrics['daily_plan_pct'] }}%</span>
            </div>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 mb-3">Retention & Growth</h3>
        <div class="space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Expiring &lt;7 hari</span>
                <span class="font-mono font-semibold {{ $metrics['expiring_7d'] > 0 ? 'text-amber-500' : 'text-slate-500' }}">{{ $metrics['expiring_7d'] }} user</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Expiring &lt;3 hari</span>
                <span class="font-mono font-semibold {{ $metrics['expiring_3d'] > 0 ? 'text-red-500' : 'text-slate-500' }}">{{ $metrics['expiring_3d'] }} user</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">User Dormant</span>
                <span class="font-mono text-slate-700 dark:text-slate-200">{{ number_format($metrics['dormant_count']) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">User Baru 30h</span>
                <span class="font-mono text-slate-700 dark:text-slate-200">{{ $metrics['new_users_30d'] }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Pembaca Gratis</span>
                <span class="font-mono {{ $metrics['free_reader_count'] >= 10 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500' }}">{{ $metrics['free_reader_count'] }} user</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const aiRoute   = '{{ route('admin.assistant.stakeholder.ai') }}';
const csrfToken = '{{ csrf_token() }}';

// ── Section config ─────────────────────────────────────────────────────────────
const SECTION_MAP = [
    { keys:['ringkasan','kondisi'], icon:'📊', label:'Ringkasan Kondisi',  border:'border-blue-200 dark:border-blue-500/20',    bg:'bg-blue-50/70 dark:bg-blue-500/[0.06]',    head:'text-blue-700 dark:text-blue-300',   numBg:'#1d4ed8' },
    { keys:['prioritas','aksi'],    icon:'🎯', label:'Prioritas Aksi',     border:'border-violet-200 dark:border-violet-500/20', bg:'bg-violet-50/70 dark:bg-violet-500/[0.06]',head:'text-violet-700 dark:text-violet-300',numBg:'#7c3aed' },
    { keys:['peringatan','risiko'], icon:'⚠️', label:'Peringatan Risiko',  border:'border-amber-200 dark:border-amber-500/20',   bg:'bg-amber-50/70 dark:bg-amber-500/[0.06]',  head:'text-amber-700 dark:text-amber-300',  numBg:'#b45309' },
];
const FALLBACK_CFG = { icon:'💡', label:'', border:'border-slate-200 dark:border-slate-700', bg:'bg-slate-50/70 dark:bg-white/[0.03]', head:'text-slate-600 dark:text-slate-300', numBg:'#475569' };

function getSectionCfg(heading) {
    const lower = heading.toLowerCase();
    for (const c of SECTION_MAP) {
        if (c.keys.some(k => lower.includes(k))) return { ...c };
    }
    return { ...FALLBACK_CFG, label: heading };
}

// ── Self-contained inline markdown formatter ───────────────────────────────────
function inlineMd(raw) {
    let s = raw
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    s = s.replace(/\*\*\*(.+?)\*\*\*/g, '<strong class="font-bold text-slate-900 dark:text-white"><em>$1</em></strong>');
    s = s.replace(/\*\*(.+?)\*\*/g,     '<strong class="font-semibold text-slate-900 dark:text-white">$1</strong>');
    s = s.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em class="italic text-slate-500 dark:text-slate-400">$1</em>');
    s = s.replace(/`([^`]+)`/g,         '<code class="font-mono text-[11px] bg-slate-100 dark:bg-white/10 text-violet-600 dark:text-violet-300 px-1.5 py-0.5 rounded">$1</code>');
    return s;
}

// ── Block-level section body renderer ─────────────────────────────────────────
function renderSectionBody(body, numBg) {
    const lines = body.split('\n');
    let out = '', i = 0, n = lines.length;

    while (i < n) {
        const line = lines[i].trimEnd();

        // blank
        if (!line.trim()) { i++; continue; }

        // ### sub-heading
        if (/^###\s+/.test(line)) {
            out += `<p class="font-mono text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-3 mb-1">${inlineMd(line.replace(/^###\s+/,''))}</p>`;
            i++; continue;
        }

        // > blockquote — collect consecutive lines
        if (/^>\s*/.test(line)) {
            const qLines = [];
            while (i < n && /^>\s*/.test(lines[i].trimEnd())) {
                qLines.push(lines[i].replace(/^>\s*/,'').trimEnd());
                i++;
            }
            out += `<blockquote class="border-l-2 border-slate-300 dark:border-slate-600 pl-3 my-2 text-[12px] italic text-slate-500 dark:text-slate-400 leading-relaxed">${inlineMd(qLines.join(' '))}</blockquote>`;
            continue;
        }

        // Ordered list — collect items (with multi-line continuation)
        if (/^\d+\.\s/.test(line)) {
            const items = [];
            while (i < n) {
                const cur = lines[i].trimEnd();
                const m = cur.match(/^(\d+)\.\s+(.*)/);
                if (m) {
                    let text = m[2];
                    i++;
                    while (i < n && lines[i].trim() &&
                           !/^\d+\.\s/.test(lines[i]) &&
                           !/^[-*]\s/.test(lines[i]) &&
                           !/^>\s/.test(lines[i]) &&
                           !/^###\s/.test(lines[i])) {
                        text += ' ' + lines[i].trim();
                        i++;
                    }
                    items.push([parseInt(m[1]), text.trim()]);
                } else { break; }
            }
            out += `<div class="space-y-3 my-2">`;
            for (const [num, text] of items) {
                // Try "**Title**: desc" or "**Title** — desc" pattern
                const bm = text.match(/^\*\*(.+?)\*\*[:\s\u2013\u2014\-]+([\s\S]*)/);
                if (bm) {
                    out += `<div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-lg text-white font-mono font-bold text-[10px] flex items-center justify-center mt-0.5 shadow-sm" style="background:${numBg}">${num}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-white text-[12px] leading-snug mb-0.5">${inlineMd(bm[1].trim())}</p>
                            <p class="text-[12px] text-slate-600 dark:text-slate-300 leading-relaxed">${inlineMd(bm[2].trim())}</p>
                        </div>
                    </div>`;
                } else {
                    out += `<div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-lg text-white font-mono font-bold text-[10px] flex items-center justify-center mt-0.5 shadow-sm" style="background:${numBg}">${num}</span>
                        <div class="flex-1 text-[12px] text-slate-700 dark:text-slate-200 leading-relaxed pt-0.5">${inlineMd(text)}</div>
                    </div>`;
                }
            }
            out += `</div>`;
            continue;
        }

        // Unordered list — collect consecutive bullets
        if (/^[-*]\s/.test(line)) {
            const bullets = [];
            while (i < n && /^[-*]\s/.test(lines[i].trimEnd())) {
                bullets.push(lines[i].replace(/^[-*]\s+/,'').trimEnd());
                i++;
            }
            out += `<ul class="space-y-2 my-2">`;
            for (const b of bullets) {
                out += `<li class="flex gap-2 items-start text-[12px] text-slate-700 dark:text-slate-200 leading-relaxed">
                    <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 mt-[5px]"></span>
                    <span>${inlineMd(b)}</span>
                </li>`;
            }
            out += `</ul>`;
            continue;
        }

        // Paragraph — collect until blank/list/special line
        const paraLines = [];
        while (i < n) {
            const cur = lines[i].trimEnd();
            if (!cur.trim() || /^(\d+\.|[-*]|>|###)\s/.test(cur)) break;
            paraLines.push(cur);
            i++;
        }
        if (paraLines.length) {
            out += `<p class="text-[13px] text-slate-700 dark:text-slate-200 leading-relaxed mb-2 last:mb-0">${inlineMd(paraLines.join(' '))}</p>`;
        }
    }
    return out || '<p class="text-[12px] text-slate-400 italic">—</p>';
}

// ── Top-level narrative renderer ───────────────────────────────────────────────
function renderAiNarrative(text) {
    const hasSections = /^##\s+/m.test(text);

    // No ## headers → plain prose
    if (!hasSections) {
        return `<div class="space-y-2">${renderSectionBody(text, FALLBACK_CFG.numBg)}</div>`;
    }

    const parts = text.split(/^##\s+/m).filter(s => s.trim());
    let html = '<div class="space-y-4">';

    for (const section of parts) {
        const nl      = section.indexOf('\n');
        const heading = (nl >= 0 ? section.slice(0, nl) : section).trim();
        const body    = nl >= 0 ? section.slice(nl + 1).trim() : '';
        const cfg     = getSectionCfg(heading);

        html += `<div class="rounded-2xl border ${cfg.border} ${cfg.bg} overflow-hidden">`;
        html += `<div class="flex items-center gap-2.5 px-4 py-2.5 border-b ${cfg.border} bg-white/40 dark:bg-white/[0.03]">`;
        html += `<span class="text-sm leading-none">${cfg.icon}</span>`;
        html += `<span class="font-mono text-[11px] font-bold ${cfg.head} uppercase tracking-wider">${cfg.label || heading}</span>`;
        html += `</div>`;
        html += `<div class="px-4 py-4 space-y-1">${body ? renderSectionBody(body, cfg.numBg) : '<p class="text-[12px] text-slate-400 italic">—</p>'}</div>`;
        html += `</div>`;
    }

    html += '</div>';
    return html;
}

// ── Generate / Refresh ────────────────────────────────────────────────────────
function generateAiInsight() {
    const btn     = document.getElementById('ai-generate-btn');
    const btnText = document.getElementById('ai-btn-text');
    const btnIcon = document.getElementById('ai-btn-icon');
    const loading = document.getElementById('ai-loading');
    const errWrap = document.getElementById('ai-error');
    const errMsg  = document.getElementById('ai-error-msg');
    const result  = document.getElementById('ai-result-wrap');
    const empty   = document.getElementById('ai-empty');
    const metaEl  = document.getElementById('ai-meta');

    btn.disabled = true;
    btnText.textContent = 'Menganalisis...';
    btnIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>`;
    btnIcon.classList.add('animate-spin');
    loading.classList.remove('hidden');
    errWrap.classList.add('hidden');
    result.classList.add('hidden');
    if (empty) empty.classList.add('hidden');

    fetch(aiRoute, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            errMsg.textContent = data.error;
            errWrap.classList.remove('hidden');
            return;
        }
        if (metaEl && data.cached_at) {
            const fromCache = data.from_cache ? ' · dari cache' : ' · baru dibuat';
            metaEl.innerHTML = `<span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/20 px-2.5 py-1 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ${data.cached_at}${fromCache}
            </span>`;
        }
        document.getElementById('ai-narrative').innerHTML = renderAiNarrative(data.narrative);
        result.classList.remove('hidden');
    })
    .catch(err => {
        errMsg.textContent = 'Gagal terhubung ke server: ' + err.message;
        errWrap.classList.remove('hidden');
    })
    .finally(() => {
        loading.classList.add('hidden');
        btn.disabled = false;
        btnText.textContent = 'Refresh Analisis';
        btnIcon.classList.remove('animate-spin');
        btnIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>`;
    });
}
</script>
@endpush
