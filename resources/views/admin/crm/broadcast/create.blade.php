@extends('layouts.admin')
@section('title', 'Broadcast Email')
@section('page-title', 'Broadcast Email')

@section('content')
<div class="max-w-2xl">
    <div class="flat-card p-6">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Kirim Email ke Grup</h2>

        <form action="{{ route('admin.crm.broadcast.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Nama Campaign --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama Campaign <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Promo Ramadan 2026"
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Grup Penerima --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Grup Penerima <span class="text-red-500">*</span>
                </label>
                <select name="group_id" id="group_id" onchange="previewGroup()" required
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('group_id') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                    <option value="">— Pilih Grup —</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }} ({{ $group->type === 'static' ? 'Static' : 'Dinamis' }})
                        </option>
                    @endforeach
                </select>
                @error('group_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror

                {{-- Preview penerima --}}
                <div id="group-preview" class="hidden mt-2 p-3 bg-slate-50 dark:bg-white/[0.03] rounded-xl border border-slate-200 dark:border-white/[0.06]">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <span id="preview-count" class="font-semibold text-blue-600 dark:text-blue-400"></span> penerima ditemukan
                    </p>
                    <ul id="preview-list" class="mt-1 space-y-0.5"></ul>
                </div>
            </div>

            {{-- Template Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Template Email <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <select name="template_id" id="template_id" required
                        class="flex-1 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('template_id') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                        <option value="">— Pilih Template —</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" {{ old('template_id') == $tmpl->id ? 'selected' : '' }}>
                                {{ $tmpl->name }}
                            </option>
                        @endforeach
                    </select>
                    <a id="preview-template-link" href="#" target="_blank"
                        class="h-10 px-3 inline-flex items-center text-xs text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                        Preview
                    </a>
                </div>
                @error('template_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Subject --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Subject Email <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Subject email..."
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
                @error('subject')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Jadwal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Jadwal Pengiriman
                </label>
                <div class="flex items-center gap-3 mb-2">
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="now" checked onchange="toggleSchedule(this)" class="accent-blue-600"> Kirim Sekarang
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="radio" name="send_type" value="scheduled" onchange="toggleSchedule(this)" class="accent-blue-600"> Jadwalkan
                    </label>
                </div>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                    value="{{ old('scheduled_at') }}"
                    class="hidden block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
                @error('scheduled_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Kirim Campaign
                </button>
                <a href="{{ route('admin.crm.campaigns.index') }}"
                    class="h-10 px-5 flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSchedule(radio) {
    const el = document.getElementById('scheduled_at');
    if (radio.value === 'scheduled') {
        el.classList.remove('hidden');
        el.required = true;
    } else {
        el.classList.add('hidden');
        el.required = false;
        el.value = '';
    }
}

function previewGroup() {
    const groupId = document.getElementById('group_id').value;
    const previewBox = document.getElementById('group-preview');

    if (!groupId) {
        previewBox.classList.add('hidden');
        return;
    }

    fetch('{{ route('admin.crm.broadcast.preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ group_id: groupId }),
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('preview-count').textContent = data.count;
        const list = document.getElementById('preview-list');
        list.innerHTML = data.sample.map(r =>
            `<li class="text-xs text-slate-500 dark:text-slate-400">${r.email}${r.name ? ' — ' + r.name : ''}</li>`
        ).join('');
        if (data.count > 5) {
            list.innerHTML += `<li class="text-xs text-slate-400 dark:text-slate-500">...dan ${data.count - 5} lainnya</li>`;
        }
        previewBox.classList.remove('hidden');
    });
}

document.getElementById('template_id').addEventListener('change', function() {
    const link = document.getElementById('preview-template-link');
    if (this.value) {
        link.href = '/admin/crm/templates/' + this.value + '/preview';
    } else {
        link.href = '#';
    }
});
</script>
@endpush
