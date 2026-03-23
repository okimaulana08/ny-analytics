<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Novelya Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono, h1, h2 { font-family: 'Fira Code', monospace; }
    </style>
</head>
<body class="h-full bg-slate-100 dark:bg-[#0d0f14] transition-colors duration-300">

    {{-- Dark mode toggle --}}
    <button onclick="toggleDark()" class="fixed top-4 right-4 z-10 w-9 h-9 rounded-xl bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/10 flex items-center justify-center transition-all duration-200 cursor-pointer shadow-sm">
        <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
    </button>

    <div class="min-h-full flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">

            {{-- Logo --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center mb-4 shadow-lg shadow-blue-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h1 class="font-mono text-lg font-bold text-slate-900 dark:text-white tracking-tight">Novelya Analytics</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masuk ke admin dashboard</p>
            </div>

            {{-- Card --}}
            <div class="bg-white dark:bg-white/[0.04] backdrop-blur-sm rounded-2xl border border-slate-200 dark:border-white/[0.08] shadow-xl shadow-slate-200/50 dark:shadow-black/30 p-7">

                {{-- Error --}}
                @if($errors->any())
                <div class="mb-5 flex items-start gap-2.5 px-3.5 py-3 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-500/20 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="admin@novelya.id"
                            class="block w-full h-10 px-3.5 text-sm bg-slate-50 dark:bg-white/5 border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 outline-none transition-all duration-150
                            focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400
                            {{ $errors->has('email') ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-white/10' }}">
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full h-10 pl-3.5 pr-10 text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 outline-none transition-all duration-150 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400">
                            <button type="button" onclick="togglePass()" class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full h-10 mt-1 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 hover:shadow-blue-500/30 transition-all duration-150 cursor-pointer">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 dark:text-slate-600 mt-6">
                © {{ date('Y') }} Novelya Analytics · Internal Use Only
            </p>
        </div>
    </div>

<script>
    function toggleDark() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }
    (function() {
        const t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
    function togglePass() {
        const el = document.getElementById('password');
        el.type = el.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>
