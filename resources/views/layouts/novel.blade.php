<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Novel Generator') — Novelya</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('head-scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        mono: ['"Fira Code"', 'monospace'],
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Lora', 'serif'],
                    },
                    colors: {
                        novel: {
                            bg: '#0e0c12',
                            surface: '#1a1625',
                            surface2: '#221d30',
                            gold: '#d4a04a',
                            'gold-light': '#f0c87a',
                            purple: '#7c5cbf',
                            'purple-light': '#a688e0',
                            text: '#e8e0d0',
                            muted: '#8a7f9a',
                            dim: '#5a5368',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500;600&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { background: #0e0c12; color: #e8e0d0; font-family: 'Inter', sans-serif; }
        .font-mono, .font-code { font-family: 'Fira Code', monospace; }
        .font-serif { font-family: 'Lora', serif; }

        /* Novel scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(212,160,74,0.2); border-radius: 99px; }

        /* Stage pipeline */
        .stage-connector { flex: 1; height: 2px; background: rgba(212,160,74,0.15); margin: 0 8px; transition: background 0.3s; }
        .stage-connector.done { background: rgba(212,160,74,0.6); }

        /* Generating animation */
        @keyframes ai-thinking {
            0%   { box-shadow: 0 0 12px rgba(124,92,191,0.3); border-color: rgba(124,92,191,0.5); }
            50%  { box-shadow: 0 0 24px rgba(212,160,74,0.4); border-color: rgba(212,160,74,0.6); }
            100% { box-shadow: 0 0 12px rgba(124,92,191,0.3); border-color: rgba(124,92,191,0.5); }
        }
        .generating { animation: ai-thinking 2s ease-in-out infinite; }

        /* Gold border glow on hover for cards */
        .novel-card { background: #1a1625; border: 1px solid rgba(212,160,74,0.1); border-radius: 16px; transition: all 0.2s; }
        .novel-card:hover { border-color: rgba(212,160,74,0.3); box-shadow: 0 0 0 1px rgba(212,160,74,0.1), 0 8px 32px rgba(0,0,0,0.4); transform: translateY(-2px); }

        /* Story cover gradients */
        .cover-perselingkuhan { background: linear-gradient(135deg, #7f1d1d 0%, #be123c 100%); }
        .cover-poligami { background: linear-gradient(135deg, #78350f 0%, #d97706 100%); }
        .cover-kdrt { background: linear-gradient(135deg, #1e293b 0%, #475569 100%); }
        .cover-pernikahan_kontrak { background: linear-gradient(135deg, #4c1d95 0%, #a855f7 100%); }
        .cover-drama_rumah_tangga { background: linear-gradient(135deg, #831843 0%, #ec4899 100%); }
        .cover-default { background: linear-gradient(135deg, #1e1b2e 0%, #7c5cbf 100%); }

        /* Approval bar */
        .approval-bar { position: sticky; bottom: 0; background: linear-gradient(to top, #0e0c12 70%, transparent); padding: 24px 0 32px; }

        /* Novel prose content display */
        .prose-novel { font-family: 'Lora', serif; line-height: 1.85; max-width: 68ch; color: #e8e0d0; font-size: 1rem; }
        .prose-novel p { margin-bottom: 1.2em; }

        /* Input/textarea in novel theme */
        .novel-input { background: rgba(255,255,255,0.04); border: 1px solid rgba(212,160,74,0.15); color: #e8e0d0; border-radius: 10px; padding: 10px 14px; width: 100%; transition: border-color 0.2s; outline: none; }
        .novel-input:focus { border-color: rgba(212,160,74,0.4); box-shadow: 0 0 0 3px rgba(212,160,74,0.08); }
        .novel-input::placeholder { color: #5a5368; }

        /* Buttons */
        .btn-gold { background: linear-gradient(135deg, #d4a04a, #b8872e); color: #0e0c12; font-weight: 600; border-radius: 10px; padding: 10px 20px; transition: all 0.2s; border: none; cursor: pointer; }
        .btn-gold:hover { background: linear-gradient(135deg, #f0c87a, #d4a04a); box-shadow: 0 4px 16px rgba(212,160,74,0.3); }
        .btn-outline { background: transparent; border: 1px solid rgba(212,160,74,0.3); color: #d4a04a; border-radius: 10px; padding: 10px 20px; transition: all 0.2s; cursor: pointer; }
        .btn-outline:hover { border-color: rgba(212,160,74,0.6); background: rgba(212,160,74,0.08); }
        .btn-ghost { background: transparent; border: 1px solid rgba(255,255,255,0.08); color: #8a7f9a; border-radius: 10px; padding: 10px 20px; transition: all 0.2s; cursor: pointer; }
        .btn-ghost:hover { border-color: rgba(255,255,255,0.15); color: #e8e0d0; }
        .btn-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; border-radius: 10px; padding: 10px 20px; transition: all 0.2s; cursor: pointer; }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }

        /* Status badges novel theme */
        .badge-draft { background: rgba(100,100,120,0.2); color: #8a7f9a; }
        .badge-pending { background: rgba(26,58,92,0.5); color: #74b3ce; }
        .badge-ready { background: rgba(123,79,18,0.4); color: #ffd166; }
        .badge-approved { background: rgba(45,106,79,0.4); color: #95d5b2; }
        .badge-generating { background: rgba(124,92,191,0.2); color: #a688e0; }
        .badge-failed { background: rgba(107,45,45,0.4); color: #f4a0a0; }

        select.novel-input option { background: #221d30; color: #e8e0d0; }

        /* Topbar nav link */
        .top-nav-link { color: rgba(232,224,208,0.5); font-size: 13px; padding: 6px 12px; border-radius: 8px; transition: all 0.15s; text-decoration: none; }
        .top-nav-link:hover { color: #e8e0d0; background: rgba(255,255,255,0.05); }
        .top-nav-link.active { color: #d4a04a; background: rgba(212,160,74,0.08); }
    </style>
    @stack('styles')
</head>
<body class="h-full min-h-screen">

{{-- TOPBAR --}}
<header class="fixed top-0 left-0 right-0 z-50 h-14 flex items-center px-5 border-b border-white/[0.06]" style="background: rgba(14,12,18,0.95); backdrop-filter: blur(12px);">
    {{-- Logo / Back --}}
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 mr-6">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #7c5cbf, #d4a04a);">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <span class="font-mono text-sm font-semibold" style="color: #d4a04a;">Novel</span>
        <span class="text-xs font-mono" style="color: #5a5368;">Generator</span>
    </a>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 flex-1 overflow-hidden">
        @hasSection('breadcrumb')
            <nav class="flex items-center gap-1 text-sm overflow-hidden">
                @yield('breadcrumb')
            </nav>
        @endif
    </div>

    {{-- Right: token count + admin back --}}
    <div class="flex items-center gap-3 ml-4">
        @hasSection('header-right')
            @yield('header-right')
        @endif
        <a href="{{ route('admin.dashboard') }}" class="top-nav-link flex items-center gap-1.5 text-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Admin
        </a>
    </div>
</header>

{{-- Flash messages --}}
@if(session('success') || session('error'))
<div class="fixed top-14 left-0 right-0 z-40 flex justify-center px-4 pt-3 pointer-events-none"
    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="transition duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    @if(session('success'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium pointer-events-auto" style="background: rgba(45,106,79,0.4); border: 1px solid rgba(149,213,178,0.3); color: #95d5b2; max-width: 480px;">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium pointer-events-auto" style="background: rgba(107,45,45,0.4); border: 1px solid rgba(244,160,160,0.3); color: #f4a0a0; max-width: 480px;">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif
</div>
@endif

{{-- MAIN CONTENT --}}
<main class="pt-14 min-h-screen">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
