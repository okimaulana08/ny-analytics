<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $story->title_draft ?? $story->title ?? 'Novel' }} — Export</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.85;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Screen toolbar ── */
        .screen-toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #0e0c12; color: #e8e0d0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 20px;
            font-family: 'Arial', sans-serif; font-size: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.4);
        }
        .toolbar-info { display: flex; align-items: center; gap: 8px; overflow: hidden; }
        .toolbar-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 420px; color: #d4a04a; font-weight: 600; }
        .toolbar-meta { color: #5a5368; font-size: 11px; flex-shrink: 0; }
        .toolbar-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-tb { display: inline-flex; align-items: center; gap: 5px; padding: 5px 14px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; border: none; }
        .btn-print { background: #d4a04a; color: #0e0c12; }
        .btn-print:hover { background: #f0c87a; }
        .btn-close { background: rgba(255,255,255,0.08); color: #8a7f9a; }
        .btn-close:hover { background: rgba(255,255,255,0.15); color: #e8e0d0; }

        /* ── Cover ── */
        .cover {
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            min-height: 100vh; padding: 60px 48px; text-align: center;
            border-bottom: 2px solid #1a1a1a; page-break-after: always;
        }
        .cover-label { font-family: Arial, sans-serif; font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #888; margin-bottom: 32px; }
        .cover-title { font-size: 26pt; font-weight: 700; line-height: 1.3; color: #0f0f0f; margin-bottom: 16px; max-width: 580px; }
        .cover-genre { font-family: Arial, sans-serif; font-size: 10pt; color: #888; margin-bottom: 32px; }
        .cover-synopsis { font-size: 11pt; color: #444; max-width: 520px; line-height: 1.75; margin-top: 8px; }
        .cover-meta { font-family: Arial, sans-serif; font-size: 9pt; color: #aaa; margin-top: 40px; }
        .cover-badge { display: inline-block; background: #1a1a1a; color: #fff; font-family: Arial, sans-serif; font-size: 8pt; padding: 4px 14px; border-radius: 20px; margin-top: 20px; letter-spacing: 0.06em; }

        /* ── Table of Contents ── */
        .toc { max-width: 640px; margin: 0 auto; padding: 60px 48px; page-break-after: always; }
        .toc-heading { font-family: Arial, sans-serif; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.15em; color: #888; margin-bottom: 28px; }
        .toc-item { display: flex; align-items: baseline; gap: 10px; padding: 7px 0; border-bottom: 1px dotted #ddd; font-size: 10.5pt; }
        .toc-num { font-family: Arial, sans-serif; font-size: 8.5pt; color: #bbb; flex-shrink: 0; width: 36px; }
        .toc-title { flex: 1; color: #333; }

        /* ── Chapter ── */
        .chapter { max-width: 640px; margin: 0 auto; padding: 64px 48px; page-break-before: always; }
        .chapter-num { font-family: Arial, sans-serif; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.15em; color: #bbb; display: block; margin-bottom: 10px; }
        .chapter-title { font-size: 18pt; font-weight: 700; color: #111; line-height: 1.3; margin-bottom: 36px; padding-bottom: 16px; border-bottom: 1px solid #e5e5e5; }
        .chapter-body { font-size: 12pt; line-height: 1.9; color: #2a2a2a; }
        .chapter-body p { margin-bottom: 1.1em; text-indent: 1.5em; }
        .chapter-body p:first-child { text-indent: 0; }

        /* ── Footer ── */
        .print-footer { text-align: center; font-family: Arial, sans-serif; font-size: 8pt; color: #bbb; padding: 40px 48px 24px; }

        /* ── Print ── */
        @media print {
            .screen-toolbar { display: none !important; }
            body { padding-top: 0 !important; }
            @page { size: A4; margin: 22mm 20mm 24mm 20mm; }
            .chapter { padding: 0; margin: 0 auto; }
        }
        @media screen { body { padding-top: 46px; } }
    </style>
</head>
<body>

{{-- Screen toolbar --}}
<div class="screen-toolbar">
    <div class="toolbar-info">
        <svg width="15" height="15" fill="none" stroke="#d4a04a" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <span class="toolbar-title">{{ $story->title_draft ?? $story->title }}</span>
        <span class="toolbar-meta">· {{ $story->chapters->count() }} bab</span>
    </div>
    <div class="toolbar-actions">
        <button class="btn-tb btn-print" onclick="window.print()">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Simpan PDF
        </button>
        <button class="btn-tb btn-close" onclick="window.close()">Tutup</button>
    </div>
</div>

{{-- Cover --}}
<div class="cover">
    <span class="cover-label">Novelya — Novel Export</span>
    <h1 class="cover-title">{{ $story->title_draft ?? $story->title ?? 'Untitled' }}</h1>
    <p class="cover-genre">{{ $story->genreLabel() }}</p>
    @if($story->synopsis)
        <p class="cover-synopsis">{{ \Illuminate\Support\Str::limit(strip_tags($story->synopsis), 280) }}</p>
    @endif
    <span class="cover-badge">{{ $story->chapters->count() }} Bab · {{ $story->total_chapters_planned }} Direncanakan</span>
    <p class="cover-meta">Diekspor: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }} WIB</p>
</div>

{{-- Table of Contents --}}
@if($story->chapters->count() > 0)
<div class="toc">
    <p class="toc-heading">Daftar Isi</p>
    @foreach($story->chapters as $ch)
    <div class="toc-item">
        <span class="toc-num">{{ $ch->chapter_number }}</span>
        <span class="toc-title">{{ $ch->title ?: 'Bab ' . $ch->chapter_number }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Chapters --}}
@forelse($story->chapters as $ch)
<div class="chapter">
    <span class="chapter-num">Bab {{ $ch->chapter_number }}</span>
    <h2 class="chapter-title">{{ $ch->title ?: 'Bab ' . $ch->chapter_number }}</h2>
    <div class="chapter-body">
        @if($ch->content_draft)
            @foreach(array_filter(explode("\n", $ch->content_draft)) as $para)
                @if(trim($para))
                    <p>{{ trim($para) }}</p>
                @endif
            @endforeach
        @else
            <p style="color:#aaa; font-style:italic;">Konten bab tidak tersedia.</p>
        @endif
    </div>
</div>
@empty
<div style="text-align:center; padding:80px 48px; color:#aaa; font-family:Arial,sans-serif; font-size:11pt;">
    Belum ada bab yang diapprove untuk novel ini.
</div>
@endforelse

<div class="print-footer">
    Digenerate oleh Novelya Analytics &mdash; {{ now()->format('d/m/Y H:i') }}
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 800);
    });
</script>
</body>
</html>
