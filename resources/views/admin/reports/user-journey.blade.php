@extends('layouts.admin')
@section('title', 'User Journey')
@section('page-title', 'User Journey — Funnel Konversi User')

@section('content')

@php
    $funnelArr = [
        'registered' => (int) $funnel->registered,
        'ever_read'  => (int) $funnel->ever_read,
        'ever_paid'  => (int) $funnel->ever_paid,
        'renewed'    => (int) $funnel->renewed,
    ];
@endphp

{{-- Funnel KPI cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @php
        $steps = [
            ['label' => 'Registrasi',    'key' => 'registered', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'blue'],
            ['label' => 'Baca Pertama',  'key' => 'ever_read',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'violet'],
            ['label' => 'Bayar Pertama', 'key' => 'ever_paid',  'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'emerald'],
            ['label' => 'Renewal',       'key' => 'renewed',    'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => 'amber'],
        ];
        $colors = [
            'blue'    => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10',
            'violet'  => 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10',
            'emerald' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10',
            'amber'   => 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10',
        ];
    @endphp

    @foreach($steps as $i => $step)
    @php
        $count     = $funnelArr[$step['key']];
        $prevCount = $i > 0 ? $funnelArr[$steps[$i - 1]['key']] : null;
        $convRate  = ($prevCount && $prevCount > 0) ? round($count / $prevCount * 100, 1) : null;
    @endphp
    <div class="glass-card p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $colors[$step['color']] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                </svg>
            </div>
            @if($convRate !== null)
                <span class="text-xs font-semibold text-slate-400">{{ $convRate }}% konversi</span>
            @else
                <span class="text-[10px] font-semibold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Step {{ $i + 1 }}</span>
            @endif
        </div>
        <div class="text-2xl font-mono font-bold text-slate-800 dark:text-white mb-0.5">{{ number_format($count) }}</div>
        <div class="text-xs text-slate-400 font-medium">{{ $step['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Funnel visual --}}
<div class="glass-card p-6 mb-6">
    <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300 mb-5">Visualisasi Funnel</h3>
    @php $maxCount = max($funnelArr); @endphp
    <div class="space-y-3">
        @foreach($steps as $step)
        @php
            $count = $funnelArr[$step['key']];
            $pct   = $maxCount > 0 ? round($count / $maxCount * 100) : 0;
            $barColors = ['blue' => 'bg-blue-500', 'violet' => 'bg-violet-500', 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500'];
        @endphp
        <div class="flex items-center gap-4">
            <div class="w-28 text-xs text-right text-slate-500 dark:text-slate-400 font-medium flex-shrink-0">{{ $step['label'] }}</div>
            <div class="flex-1 h-8 bg-slate-100 dark:bg-white/[0.04] rounded-lg overflow-hidden">
                <div class="{{ $barColors[$step['color']] }} h-full rounded-lg flex items-center px-3 transition-all duration-500"
                     style="width: {{ $pct }}%">
                    <span class="text-white text-xs font-mono font-semibold">{{ number_format($count) }}</span>
                </div>
            </div>
            <div class="w-12 text-xs text-slate-400 font-mono text-right flex-shrink-0">{{ $pct }}%</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Time-to-convert --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="glass-card p-5 text-center">
        <div class="text-3xl font-mono font-bold text-slate-800 dark:text-white mb-1">
            {{ $timeToRead?->avg_days ?? '—' }}
        </div>
        <div class="text-xs text-slate-400">Hari: Registrasi → Baca Pertama</div>
    </div>
    <div class="glass-card p-5 text-center">
        <div class="text-3xl font-mono font-bold text-slate-800 dark:text-white mb-1">
            {{ $timeToPay?->avg_days ?? '—' }}
        </div>
        <div class="text-xs text-slate-400">Hari: Baca Pertama → Bayar</div>
    </div>
    <div class="glass-card p-5 text-center">
        <div class="text-3xl font-mono font-bold text-slate-800 dark:text-white mb-1">
            {{ $timeToRenew?->avg_days ?? '—' }}
        </div>
        <div class="text-xs text-slate-400">Hari: Bayar Pertama → Renewal</div>
    </div>
</div>

{{-- 6-month cohort table --}}
<div class="flat-card overflow-x-auto">
    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/[0.06]">
        <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300">Cohort 6 Bulan Terakhir</h3>
        <p class="text-xs text-slate-400 mt-0.5">Konversi kumulatif per bulan registrasi (per hari ini)</p>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Bulan Registrasi</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Registrasi</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">→ Baca</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">→ Bayar</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">→ Renewal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @foreach($cohorts as $cohort)
            @php
                $readPct  = $cohort['registered'] > 0 ? round($cohort['ever_read'] / $cohort['registered'] * 100) : 0;
                $paidPct  = $cohort['registered'] > 0 ? round($cohort['ever_paid'] / $cohort['registered'] * 100) : 0;
                $renewPct = $cohort['registered'] > 0 ? round($cohort['renewed']   / $cohort['registered'] * 100) : 0;
            @endphp
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5 font-mono text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $cohort['label'] }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ number_format($cohort['registered']) }}</td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ number_format($cohort['ever_read']) }}</span>
                    <span class="text-xs text-slate-400 ml-1">({{ $readPct }}%)</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ number_format($cohort['ever_paid']) }}</span>
                    <span class="text-xs {{ $paidPct >= 10 ? 'text-emerald-500' : 'text-slate-400' }} ml-1">({{ $paidPct }}%)</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ number_format($cohort['renewed']) }}</span>
                    <span class="text-xs {{ $renewPct >= 30 ? 'text-emerald-500' : 'text-slate-400' }} ml-1">({{ $renewPct }}%)</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
