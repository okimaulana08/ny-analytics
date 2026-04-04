<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bab {{ $chapter->chapter_number }} — {{ $story->title_draft ?? $story->title }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Georgia', 'Times New Roman', serif; font-size: 12pt; line-height: 1.85; color: #1a1a1a; background: #fff; }

        .screen-toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #0e0c12; color: #e8e0d0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 20px; font-family: Arial, sans-serif; font-size: 12px;
        }
        .toolbar-info { display: flex; align-items: center; gap: 8px; overflow: hidden; }
        .toolbar-title { color: #d4a04a; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }
        .toolbar-meta { color: #5a5368; font-size: 11px; flex-shrink: 0; }
        .toolbar-actions { display: flex; gap: 8px; }
        .btn-tb { display: inline-flex; align-items: center; gap: 5px; padding: 5px 14px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; border: none; }
        .btn-print { background: #d4a04a; color: #0e0c12; }
        .btn-print:hover { background: #f0c87a; }
        .btn-close { background: rgba(255,255,255,0.08); color: #8a7f9a; }

        .chapter { max-width: 640px; margin: 0 auto; padding: 60px 48px; }
        .chapter-num { font-family: Arial, sans-serif; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.15em; color: #bbb; display: block; margin-bottom: 10px; }
        .chapter-title { font-size: 20pt; font-weight: 700; color: #111; line-height: 1.3; margin-bottom: 36px; padding-bottom: 16px; border-bottom: 1px solid #e5e5e5; }
        .story-name { font-family: Arial, sans-serif; font-size: 9pt; color: #aaa; margin-bottom: 6px; display: block; }
        .chapter-body { font-size: 12pt; line-height: 1.9; color: #2a2a2a; }
        .chapter-body p { margin-bottom: 1.1em; text-indent: 1.5em; }
        .chapter-body p:first-child { text-indent: 0; }
        .print-footer { text-align: center; font-family: Arial, sans-serif; font-size: 8pt; color: #bbb; padding: 40px 48px 24px; }

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

<div class="screen-toolbar">
    <div class="toolbar-info">
        <svg width="15" height="15" fill="none" stroke="#d4a04a" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <span class="toolbar-title">{{ $story->title_draft ?? $story->title }}</span>
        <span class="toolbar-meta">· Bab {{ $chapter->chapter_number }}</span>
    </div>
    <div class="toolbar-actions">
        <button class="btn-tb btn-print" onclick="window.print()">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Simpan PDF
        </button>
        <button class="btn-tb btn-close" onclick="window.close()">Tutup</button>
    </div>
</div>

<div class="chapter">
    <span class="story-name">{{ $story->title_draft ?? $story->title }}</span>
    <span class="chapter-num">Bab {{ $chapter->chapter_number }}</span>
    <h1 class="chapter-title">{{ $chapter->title ?: 'Bab ' . $chapter->chapter_number }}</h1>
    <div class="chapter-body">
        @if($chapter->content_draft)
            @foreach(array_filter(explode("\n", $chapter->content_draft)) as $para)
                @if(trim($para))
                    <p>{{ trim($para) }}</p>
                @endif
            @endforeach
        @else
            <p style="color:#aaa; font-style:italic;">Konten bab tidak tersedia.</p>
        @endif
    </div>
</div>

<div class="print-footer">
    {{ $story->title_draft ?? $story->title }} · Bab {{ $chapter->chapter_number }} &mdash; Novelya Analytics {{ now()->format('d/m/Y') }}
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 600);
    });
</script>
</body>
</html>
