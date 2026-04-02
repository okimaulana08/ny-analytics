<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $content->title }} — PDF Export</title>
    <style>
        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #1a1a1a;
            background: #fff;
            padding: 0;
        }

        /* ── Cover ── */
        .cover {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 60px 48px;
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            page-break-after: always;
        }

        .cover-label {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #666;
            margin-bottom: 32px;
        }

        .cover-title {
            font-size: 28pt;
            font-weight: 700;
            line-height: 1.3;
            color: #0f0f0f;
            margin-bottom: 16px;
            max-width: 600px;
        }

        .cover-meta {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #888;
            margin-top: 24px;
        }

        .cover-badge {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            padding: 4px 14px;
            border-radius: 20px;
            margin-top: 20px;
            letter-spacing: 0.05em;
        }

        /* ── Synopsis ── */
        .synopsis-block {
            margin: 0 auto;
            max-width: 640px;
            padding: 48px;
            page-break-after: always;
        }

        .synopsis-block h2 {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #888;
            margin-bottom: 16px;
        }

        .synopsis-block p {
            font-size: 11pt;
            color: #333;
            line-height: 1.9;
        }

        /* ── TOC ── */
        .toc {
            max-width: 640px;
            margin: 0 auto;
            padding: 48px;
            page-break-after: always;
        }

        .toc h2 {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #888;
            margin-bottom: 24px;
        }

        .toc-item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 6px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 10pt;
            color: #333;
        }

        .toc-item .seq {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #aaa;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .toc-item .ch-title {
            flex: 1;
        }

        /* ── Chapter ── */
        .chapter {
            max-width: 640px;
            margin: 0 auto;
            padding: 60px 48px;
            page-break-before: always;
        }

        .chapter-header {
            margin-bottom: 36px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e5e5;
        }

        .chapter-num {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #aaa;
            display: block;
            margin-bottom: 8px;
        }

        .chapter-title {
            font-size: 18pt;
            font-weight: 700;
            color: #111;
            line-height: 1.35;
        }

        .chapter-body {
            font-size: 11.5pt;
            line-height: 1.9;
            color: #2a2a2a;
        }

        .chapter-body p {
            margin-bottom: 1em;
        }

        .chapter-body h1, .chapter-body h2, .chapter-body h3,
        .chapter-body h4, .chapter-body h5, .chapter-body h6 {
            font-family: 'Arial', sans-serif;
            font-weight: 700;
            margin-top: 1.4em;
            margin-bottom: 0.5em;
            color: #111;
        }

        .chapter-body strong, .chapter-body b { font-weight: 700; }
        .chapter-body em, .chapter-body i { font-style: italic; }
        .chapter-body u { text-decoration: underline; }

        .chapter-body ul, .chapter-body ol {
            margin: 0.8em 0 0.8em 1.5em;
        }

        .chapter-body li { margin-bottom: 0.3em; }

        .chapter-body img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 1em 0;
        }

        .chapter-body blockquote {
            border-left: 3px solid #ccc;
            margin: 1em 0;
            padding: 0.5em 1em;
            color: #555;
            font-style: italic;
        }

        /* ── Footer bar ── */
        .print-footer {
            text-align: center;
            font-family: 'Arial', sans-serif;
            font-size: 8pt;
            color: #aaa;
            padding: 40px 48px 20px;
        }

        /* ── Screen-only toolbar ── */
        .screen-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }

        .screen-toolbar .info {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow: hidden;
        }

        .screen-toolbar .info svg {
            flex-shrink: 0;
        }

        .screen-toolbar .book-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 400px;
            color: #cbd5e1;
        }

        .screen-toolbar .ch-count {
            flex-shrink: 0;
            color: #64748b;
            font-size: 11px;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.15s;
        }

        .btn-print {
            background: #3b82f6;
            color: #fff;
        }

        .btn-print:hover { background: #2563eb; }

        .btn-close {
            background: rgba(255,255,255,0.08);
            color: #94a3b8;
        }

        .btn-close:hover { background: rgba(255,255,255,0.15); color: #f1f5f9; }

        /* ── Print media ── */
        @media print {
            .screen-toolbar { display: none !important; }
            body { padding-top: 0 !important; }

            @page {
                size: A4;
                margin: 20mm 18mm 22mm 18mm;
            }

            .chapter { padding: 0; margin: 0 auto; }
        }

        /* ── Screen padding for toolbar ── */
        @media screen {
            body { padding-top: 48px; }
        }
    </style>
</head>
<body>

    {{-- ── Screen toolbar ── --}}
    <div class="screen-toolbar" id="screenToolbar">
        <div class="info">
            <svg width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="book-title">{{ $content->title }}</span>
            <span class="ch-count">· {{ count($chapters) }} bab</span>
        </div>
        <div class="toolbar-actions">
            <button class="btn-toolbar btn-print" onclick="window.print()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak / Simpan PDF
            </button>
            <button class="btn-toolbar btn-close" onclick="window.close()">Tutup</button>
        </div>
    </div>

    {{-- ── Cover page ── --}}
    <div class="cover">
        <span class="cover-label">Novelya — Laporan Konten</span>
        <h1 class="cover-title">{{ $content->title }}</h1>
        @if($content->synopsis)
            <p style="font-size:11pt; color:#555; max-width:500px; margin-top:12px; line-height:1.7;">
                {{ \Illuminate\Support\Str::limit(strip_tags($content->synopsis), 220) }}
            </p>
        @endif
        <div class="cover-badge">{{ count($chapters) }} Bab{{ $content->is_completed ? ' · Tamat' : ' · Ongoing' }}</div>
        <p class="cover-meta">
            Diterbitkan: {{ $content->published_at ? \Carbon\Carbon::parse($content->published_at)->isoFormat('D MMMM YYYY') : '—' }}
            &nbsp;·&nbsp;
            Diekspor: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }} WIB
        </p>
    </div>

    {{-- ── Synopsis page ── --}}
    @if($content->synopsis)
    <div class="synopsis-block">
        <h2>Sinopsis</h2>
        <p>{{ strip_tags($content->synopsis) }}</p>
    </div>
    @endif

    {{-- ── Table of Contents ── --}}
    @if(count($chapters) > 0)
    <div class="toc">
        <h2>Daftar Isi</h2>
        @foreach($chapters as $ch)
        <div class="toc-item">
            <span class="seq">{{ $ch->sequence }}</span>
            <span class="ch-title">{{ $ch->title ?: 'Bab '.$ch->sequence }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Chapters ── --}}
    @forelse($chapters as $ch)
    <div class="chapter">
        <div class="chapter-header">
            <span class="chapter-num">Bab {{ $ch->sequence }}</span>
            <h2 class="chapter-title">{{ $ch->title ?: 'Bab '.$ch->sequence }}</h2>
        </div>
        <div class="chapter-body">
            @if($ch->body)
                {!! $ch->body !!}
            @else
                <p style="color:#aaa; font-style:italic;">Konten bab tidak tersedia.</p>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center; padding:80px 48px; color:#aaa; font-family:Arial,sans-serif; font-size:11pt;">
        Belum ada bab yang dipublish untuk buku ini.
    </div>
    @endforelse

    <div class="print-footer">
        Dokumen ini digenerate oleh Novelya Analytics &mdash; {{ now()->format('d/m/Y H:i') }}
    </div>

    <script>
        // Auto-open print dialog on page load
        window.addEventListener('load', function () {
            // Small delay so the page renders fully first
            setTimeout(function () { window.print(); }, 800);
        });
    </script>
</body>
</html>
