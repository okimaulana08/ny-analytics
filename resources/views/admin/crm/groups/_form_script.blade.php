<script>
let memberIndex = {{ isset($group) && $group->type === 'static' ? $group->members->count() : 1 }};

function toggleGroupType(type) {
    document.getElementById('section-static').classList.toggle('hidden', type !== 'static');
    document.getElementById('section-dynamic').classList.toggle('hidden', type !== 'dynamic');
}

function addMember() {
    const list = document.getElementById('members-list');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 member-row';
    div.innerHTML = `
        <input type="email" name="members[${memberIndex}][email]" placeholder="email@example.com"
            class="flex-1 h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
        <input type="text" name="members[${memberIndex}][name]" placeholder="Nama (opsional)"
            class="w-48 h-10 px-3.5 text-sm rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
        <button type="button" onclick="removeMember(this)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    list.appendChild(div);
    memberIndex++;
}

function removeMember(btn) {
    const rows = document.querySelectorAll('.member-row');
    if (rows.length > 1) {
        btn.closest('.member-row').remove();
    }
}

function updateCriteriaParams() {
    const filter = document.getElementById('criteria-filter').value;
    document.getElementById('param-days').classList.toggle('hidden', !['user_baru', 'akan_expired', 'user_dorman'].includes(filter));
    document.getElementById('param-min-trx').classList.toggle('hidden', filter !== 'user_loyal');
}
</script>
