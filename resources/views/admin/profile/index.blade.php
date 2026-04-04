@extends('layouts.admin')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
@php
$colorMap = [
    'blue'   => 'from-blue-500 to-blue-700',
    'purple' => 'from-purple-500 to-purple-700',
    'green'  => 'from-emerald-500 to-emerald-700',
    'orange' => 'from-orange-400 to-orange-600',
    'pink'   => 'from-pink-500 to-pink-700',
    'red'    => 'from-red-500 to-red-700',
    'teal'   => 'from-teal-500 to-teal-700',
    'indigo' => 'from-indigo-500 to-indigo-700',
];
$currentColor = old('avatar_color', $user->avatar_color ?? 'blue');
$currentGrad  = $colorMap[$currentColor] ?? $colorMap['blue'];
@endphp

<div class="max-w-lg space-y-5">

    {{-- Profile info + avatar color --}}
    <div class="flat-card p-6">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Informasi Profil</h2>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Avatar preview --}}
            <div class="flex items-center gap-4">
                <div id="avatar-preview"
                    class="w-14 h-14 rounded-full bg-gradient-to-br {{ $currentGrad }} flex items-center justify-center text-white text-xl font-bold flex-shrink-0 transition-all duration-300">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $user->name }}</div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $user->email }}</div>
                    @if($user->isSuperAdmin())
                        <div class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Super Admin
                        </div>
                    @endif
                </div>
            </div>

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Avatar color picker --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Warna Avatar</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($colorOptions as $color)
                    <label class="cursor-pointer">
                        <input type="radio" name="avatar_color" value="{{ $color }}" class="sr-only peer"
                            {{ $currentColor === $color ? 'checked' : '' }}
                            onchange="updateAvatarPreview('{{ $color }}')">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $colorMap[$color] }} ring-2 ring-transparent peer-checked:ring-offset-2 peer-checked:ring-offset-white dark:peer-checked:ring-offset-slate-900 peer-checked:ring-blue-500 transition-all duration-150 hover:scale-110"></div>
                    </label>
                    @endforeach
                </div>
                @error('avatar_color')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                Simpan Profil
            </button>
        </form>
    </div>

    {{-- Change password --}}
    <div class="flat-card p-6">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Ubah Password</h2>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            {{-- Re-send unchanged profile fields so controller doesn't clear them --}}
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="avatar_color" value="{{ $user->avatar_color ?? 'blue' }}">

            @foreach([
                ['current_password', 'Password Saat Ini', 'Password yang sedang digunakan'],
                ['password', 'Password Baru', 'Min. 8 karakter'],
                ['password_confirmation', 'Konfirmasi Password Baru', 'Ulangi password baru'],
            ] as [$field, $label, $placeholder])
            <div>
                <label for="pw_{{ $field }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ $label }}
                </label>
                <div class="relative">
                    <input type="password" id="pw_{{ $field }}" name="{{ $field }}" placeholder="{{ $placeholder }}"
                        class="block w-full h-10 pl-3.5 pr-10 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border {{ $errors->has($field) ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-white/[0.08]' }} text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
                    <button type="button" onclick="togglePass('pw_{{ $field }}')"
                        class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error($field)
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            @endforeach

            <button type="submit"
                class="h-10 px-5 bg-slate-700 hover:bg-slate-800 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-white text-sm font-semibold rounded-xl transition-all duration-150 cursor-pointer">
                Ubah Password
            </button>
        </form>
    </div>

    {{-- Last login info --}}
    @if($user->last_login_at)
    <p class="text-xs text-slate-400 dark:text-slate-500 px-1">
        Login terakhir: {{ $user->last_login_at->diffForHumans() }} — {{ $user->last_login_at->format('d/m/Y H:i') }}
    </p>
    @endif

</div>
@endsection

@push('scripts')
<script>
const colorGradients = @json($colorMap);

function updateAvatarPreview(colorKey) {
    const preview = document.getElementById('avatar-preview');
    const gradClass = colorGradients[colorKey];
    if (! gradClass) { return; }

    // Remove all existing gradient classes
    Object.values(colorGradients).forEach(cls => {
        cls.split(' ').forEach(c => preview.classList.remove(c));
    });

    // Add the newly selected ones
    gradClass.split(' ').forEach(c => preview.classList.add(c));
}

function togglePass(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
