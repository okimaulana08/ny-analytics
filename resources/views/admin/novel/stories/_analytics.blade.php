@php
    $totalCost       = $analyticsData['total_cost'] ?? 0;
    $costPerStage    = $analyticsData['cost_per_stage'] ?? [];
    $tokensPerStage  = $analyticsData['tokens_per_stage'] ?? [];
    $costPerChapter  = $analyticsData['cost_per_chapter'] ?? [];
    $avgCost         = $analyticsData['avg_cost_per_chapter'] ?? 0;
    $usages          = $analyticsData['usages'] ?? collect();
    $highRegenChaps  = collect($costPerChapter)->filter(fn($c) => ($c['generation_count'] ?? 0) >= 3);
@endphp

<div class="novel-card mb-5" x-data="{ open: false }">
    {{-- Header (always visible, click to toggle) --}}
    <div class="flex items-center justify-between p-5 cursor-pointer" @click="open = !open">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" style="color: #7c5cbf;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h2 class="font-mono text-sm font-semibold" style="color: #d4a04a;">Analitik Generasi</h2>
            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full" style="background: rgba(124,92,191,0.15); color: #a688e0;">
                ${{ number_format($totalCost, 4) }} total
            </span>
        </div>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    {{-- Collapsible body --}}
    <div x-show="open" x-cloak class="px-5 pb-5 space-y-5" style="border-top: 1px solid rgba(255,255,255,0.05);">

        {{-- Cost summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-4">
            <div class="rounded-xl p-3" style="background: rgba(212,160,74,0.08); border: 1px solid rgba(212,160,74,0.2);">
                <p class="text-[10px] font-mono mb-1" style="color: #8a7f9a;">TOTAL BIAYA</p>
                <p class="text-lg font-mono font-semibold" style="color: #d4a04a;">${{ number_format($totalCost, 4) }}</p>
                <p class="text-[10px]" style="color: #5a5368;">{{ number_format($story->total_input_tokens + $story->total_output_tokens) }} tokens</p>
            </div>
            <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-[10px] font-mono mb-1" style="color: #8a7f9a;">RINGKASAN</p>
                <p class="text-sm font-mono font-semibold" style="color: #e8e0d0;">${{ number_format($costPerStage['overview'] ?? 0, 4) }}</p>
                <p class="text-[10px]" style="color: #5a5368;">
                    {{ number_format(($tokensPerStage['overview']['in'] ?? 0) + ($tokensPerStage['overview']['out'] ?? 0)) }} tok
                </p>
            </div>
            <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-[10px] font-mono mb-1" style="color: #8a7f9a;">OUTLINE</p>
                <p class="text-sm font-mono font-semibold" style="color: #e8e0d0;">${{ number_format($costPerStage['outline'] ?? 0, 4) }}</p>
                <p class="text-[10px]" style="color: #5a5368;">
                    {{ number_format(($tokensPerStage['outline']['in'] ?? 0) + ($tokensPerStage['outline']['out'] ?? 0)) }} tok
                </p>
            </div>
            <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-[10px] font-mono mb-1" style="color: #8a7f9a;">KONTEN</p>
                <p class="text-sm font-mono font-semibold" style="color: #e8e0d0;">${{ number_format($costPerStage['content'] ?? 0, 4) }}</p>
                <p class="text-[10px]" style="color: #5a5368;">Avg/bab: ${{ number_format($avgCost, 4) }}</p>
            </div>
        </div>

        {{-- Regen warning --}}
        @if($highRegenChaps->count() > 0)
        <div class="rounded-xl p-3" style="background: rgba(107,45,45,0.2); border: 1px solid rgba(244,160,160,0.15);">
            <p class="text-xs font-mono font-semibold mb-1" style="color: #f4a0a0;">⚠ Bab dengan regenerasi tinggi (≥ 3x)</p>
            <div class="flex flex-wrap gap-2">
                @foreach($highRegenChaps as $num => $ch)
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded" style="background: rgba(244,160,160,0.1); color: #f4a0a0;">
                    Bab {{ $num }} ({{ $ch['generation_count'] }}x)
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Charts row --}}
        @if(count($costPerChapter) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Bar chart: cost per chapter --}}
            <div class="lg:col-span-2 rounded-xl p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-[10px] font-mono mb-3" style="color: #8a7f9a;">BIAYA PER BAB (USD)</p>
                <div style="height: 200px; position: relative;">
                    <canvas id="costPerChapterChart"></canvas>
                </div>
            </div>

            {{-- Doughnut: tokens per stage --}}
            <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-[10px] font-mono mb-3" style="color: #8a7f9a;">TOKEN PER STAGE</p>
                <div style="height: 160px; position: relative;">
                    <canvas id="tokenStageChart"></canvas>
                </div>
                <div class="mt-3 space-y-1.5">
                    @foreach(['overview' => ['label' => 'Ringkasan', 'color' => '#d4a04a'], 'outline' => ['label' => 'Outline', 'color' => '#7c5cbf'], 'content' => ['label' => 'Konten', 'color' => '#4ade80']] as $stage => $meta)
                    @php $stageTotal = ($tokensPerStage[$stage]['in'] ?? 0) + ($tokensPerStage[$stage]['out'] ?? 0); @endphp
                    @if($stageTotal > 0)
                    <div class="flex items-center justify-between text-[10px] font-mono">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full" style="background: {{ $meta['color'] }};"></div>
                            <span style="color: #8a7f9a;">{{ $meta['label'] }}</span>
                        </div>
                        <span style="color: #e8e0d0;">{{ number_format($stageTotal) }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        @if($usages->count() > 0)
        <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
            <p class="text-[10px] font-mono mb-3" style="color: #8a7f9a;">TIMELINE GENERASI</p>
            <div class="space-y-1.5" style="max-height: 240px; overflow-y: auto;">
                @foreach($usages as $u)
                <div class="flex items-center gap-3 text-[10px] font-mono py-1.5 px-2 rounded-lg" style="background: rgba(255,255,255,0.02);">
                    <span style="color: {{ $u->was_successful ? '#95d5b2' : '#f4a0a0' }};">{{ $u->was_successful ? '✓' : '✗' }}</span>
                    <span class="px-1.5 py-0.5 rounded" style="background: rgba(124,92,191,0.15); color: #a688e0; min-width: 54px; text-align: center;">{{ $u->stage }}</span>
                    @if($u->novel_chapter_id)
                    <span style="color: #5a5368;">Bab {{ optional($story->chapters->firstWhere('id', $u->novel_chapter_id))->chapter_number ?? '?' }}</span>
                    @endif
                    <span style="color: #5a5368;">{{ $u->created_at->format('d/m H:i') }}</span>
                    <span style="color: #8a7f9a;">{{ number_format($u->input_tokens) }} in / {{ number_format($u->output_tokens) }} out</span>
                    <span class="ml-auto" style="color: #d4a04a;">${{ number_format($u->estimated_cost_usd, 4) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
(function () {
    const costData = @json(array_values($costPerChapter));
    const tokensStage = @json($tokensPerStage);

    const darkGrid = 'rgba(255,255,255,0.06)';
    const textColor = '#8a7f9a';

    // Bar chart — cost per chapter
    const barCtx = document.getElementById('costPerChapterChart');
    if (barCtx && costData.length) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: costData.map((c, i) => 'Bab ' + (i + 1)),
                datasets: [{
                    data: costData.map(c => parseFloat(c.cost).toFixed(6)),
                    backgroundColor: costData.map(c =>
                        c.status === 'approved' ? 'rgba(74,222,128,0.55)' :
                        c.status === 'revision_requested' ? 'rgba(244,160,160,0.55)' :
                        'rgba(212,160,74,0.45)'
                    ),
                    borderColor: costData.map(c =>
                        c.status === 'approved' ? '#4ade80' :
                        c.status === 'revision_requested' ? '#f4a0a0' :
                        '#d4a04a'
                    ),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: {
                    callbacks: {
                        label: ctx => '$' + parseFloat(ctx.raw).toFixed(6) + ' · ' + costData[ctx.dataIndex].generation_count + 'x gen'
                    }
                }},
                scales: {
                    x: { ticks: { color: textColor, font: { size: 9 } }, grid: { color: darkGrid } },
                    y: { ticks: { color: textColor, font: { size: 9 }, callback: v => '$' + v }, grid: { color: darkGrid } }
                }
            }
        });
    }

    // Doughnut — tokens per stage
    const donutCtx = document.getElementById('tokenStageChart');
    if (donutCtx) {
        const stageLabels = ['Ringkasan', 'Outline', 'Konten'];
        const stageKeys = ['overview', 'outline', 'content'];
        const stageTotals = stageKeys.map(k => (tokensStage[k]?.in ?? 0) + (tokensStage[k]?.out ?? 0));
        const hasData = stageTotals.some(v => v > 0);
        if (hasData) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: stageLabels,
                    datasets: [{
                        data: stageTotals,
                        backgroundColor: ['rgba(212,160,74,0.7)', 'rgba(124,92,191,0.7)', 'rgba(74,222,128,0.7)'],
                        borderColor: ['#d4a04a', '#7c5cbf', '#4ade80'],
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw.toLocaleString() + ' tok' } }
                    }
                }
            });
        }
    }
})();
</script>
@endpush
