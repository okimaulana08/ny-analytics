window.toggleSchedule = function (radio) {
    var el = document.getElementById('scheduled_at');
    if (radio.value === 'scheduled') { el.classList.remove('hidden'); el.required = true; }
    else { el.classList.add('hidden'); el.required = false; el.value = ''; }
};

window.broadcastForm = function () {
    var routes = window._crmRoutes || {};
    var initialGroupId = document.getElementById('_init_group_id') ? document.getElementById('_init_group_id').value : '';
    var initialTemplateId = document.getElementById('_init_template_id') ? document.getElementById('_init_template_id').value : '';

    return {
        groupId: initialGroupId,
        templateId: initialTemplateId,
        groupLoading: false,
        groupCount: 0,
        members: [],
        excluded: [],
        extras: [],
        excludeQ: '',
        excludeResults: [],
        manualQ: '',
        manualResults: [],
        manualTimer: null,
        previewQ: '',
        previewResults: [],
        previewUser: null,
        previewOpen: false,
        previewLoading: false,
        previewLabel: '',

        get effectiveCount() {
            return this.groupCount - this.excluded.length + this.extras.length;
        },

        async onGroupChange() {
            if (!this.groupId) {
                this.groupCount = 0;
                this.members = [];
                this.excluded = [];
                return;
            }
            this.groupLoading = true;
            this.excluded = [];
            this.extras = [];
            try {
                var res = await fetch(routes.broadcastPreview, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ group_id: this.groupId }),
                });
                var data = await res.json();
                this.groupCount = data.count;
                this.members = data.members || [];
            } finally {
                this.groupLoading = false;
            }
        },

        filterExclude() {
            var q = this.excludeQ.toLowerCase().trim();
            if (!q) { this.excludeResults = []; return; }
            var excludedEmails = new Set(this.excluded.map(function (e) { return e.email; }));
            this.excludeResults = this.members
                .filter(function (m) {
                    return !excludedEmails.has(m.email) &&
                        (m.email.toLowerCase().indexOf(q) !== -1 || (m.name || '').toLowerCase().indexOf(q) !== -1);
                })
                .slice(0, 10);
        },

        addExclusion: function (m) {
            if (!this.excluded.find(function (e) { return e.email === m.email; })) {
                this.excluded.push({ email: m.email, name: m.name });
            }
            this.excludeQ = '';
            this.excludeResults = [];
        },

        removeExclusion: function (e) {
            this.excluded = this.excluded.filter(function (x) { return x.email !== e.email; });
        },

        searchManual: function () {
            var self = this;
            clearTimeout(this.manualTimer);
            var q = this.manualQ.trim();
            if (q.length < 2) { this.manualResults = []; return; }
            this.manualTimer = setTimeout(async function () {
                var url = new URL(routes.searchUsers, location.origin);
                url.searchParams.set('q', q);
                var res = await fetch(url, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                var data = await res.json();
                var addedEmails = new Set(self.extras.map(function (e) { return e.email; }));
                self.manualResults = data.filter(function (u) { return !addedEmails.has(u.email); });
            }, 300);
        },

        addManualUser: function (u) {
            if (!this.extras.find(function (e) { return e.email === u.email; })) {
                this.extras.push({ email: u.email, name: u.name, user_id: u.user_id });
            }
            this.manualQ = '';
            this.manualResults = [];
        },

        removeExtra: function (e) {
            this.extras = this.extras.filter(function (x) { return x.email !== e.email; });
        },

        onTemplateChange: function () {
            var sel = document.querySelector('select[name="template_id"]');
            var subject = sel && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].dataset.subject || '' : '';
            var subjectInput = document.getElementById('subject');
            if (subject && !subjectInput.value) {
                subjectInput.value = subject;
            } else if (subject && subjectInput.value && confirm('Timpa subject dengan default dari template?')) {
                subjectInput.value = subject;
            }
        },

        filterPreview: function () {
            var q = this.previewQ.toLowerCase().trim();
            if (!q || !this.members.length) { this.previewResults = []; return; }
            this.previewResults = this.members
                .filter(function (m) {
                    return m.email.toLowerCase().indexOf(q) !== -1 || (m.name || '').toLowerCase().indexOf(q) !== -1;
                })
                .slice(0, 10);
        },

        selectPreviewUser: function (m) {
            this.previewUser = m;
            this.previewQ = m.email;
            this.previewResults = [];
        },

        openPreview: async function () {
            if (!this.templateId) { return; }
            this.previewOpen = true;
            this.previewLoading = true;
            this.previewLabel = this.previewUser
                ? 'Data aktual: ' + (this.previewUser.name || this.previewUser.email)
                : 'Data sampel (tidak ada user dipilih)';
            try {
                var body = { template_id: this.templateId };
                if (this.previewUser) {
                    body.user_id = this.previewUser.user_id;
                    body.user_email = this.previewUser.email;
                    body.user_name = this.previewUser.name;
                }
                var res = await fetch(routes.previewForUser, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(body),
                });
                var html = await res.text();
                this.$refs.previewFrame.srcdoc = html;
            } finally {
                this.previewLoading = false;
            }
        },
    };
};
