window.aiTemplateGenerator = function () {
    return {
        open: false,
        intent: '',
        params: [],
        loading: false,
        error: '',
        success: false,
        presets: [
            { key: 'name',           description: 'Nama lengkap pengguna' },
            { key: 'email',          description: 'Alamat email pengguna' },
            { key: 'expiry_date',    description: 'Tanggal berakhir subscription (contoh: 31 Desember 2025)' },
            { key: 'plan_name',      description: 'Nama paket subscription (contoh: Premium Bulanan)' },
            { key: 'invoice_url',    description: 'Link halaman pembayaran/invoice' },
            { key: 'payment_status', description: 'Status pembayaran (pending/paid/expired)' },
            { key: 'story_title',    description: 'Judul cerita yang direkomendasikan' },
            { key: 'story_cover',    description: 'URL gambar cover cerita' },
            { key: 'story_synopsis', description: 'Sinopsis singkat cerita' },
            { key: 'story_url',      description: 'Link halaman cerita di Novelya' },
        ],
        addPreset: function (preset) {
            if (!this.params.some(function (p) { return p.key === preset.key; })) {
                this.params.push({ key: preset.key, description: preset.description });
            }
        },
        generate: async function () {
            this.error = '';
            this.success = false;
            this.loading = true;
            try {
                var res = await fetch(window._crmRoutes.aiGenerate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        intent: this.intent,
                        params: this.params.filter(function (p) { return p.key.trim(); }),
                    }),
                });
                var data = await res.json();
                if (!res.ok) {
                    this.error = data.error || 'Gagal generate template.';
                    return;
                }
                if (data.subject)      document.getElementById('field_subject').value      = data.subject;
                if (data.preview_text) document.getElementById('field_preview_text').value = data.preview_text;
                if (data.html_body)    document.getElementById('field_html_body').value    = data.html_body;
                var nameField = document.getElementById('field_name');
                if (nameField && !nameField.value.trim()) {
                    nameField.value = this.intent.substring(0, 60).trim();
                }
                this.success = true;
                var self = this;
                setTimeout(function () { self.success = false; }, 4000);
            } catch (e) {
                this.error = 'Terjadi kesalahan jaringan.';
            } finally {
                this.loading = false;
            }
        },
    };
};

window.openTemplatePreview = function () {
    var html = document.getElementById('field_html_body').value;
    if (!html.trim()) { alert('HTML body kosong.'); return; }
    var win = window.open('', '_blank');
    win.document.write('Memuat preview...');
    fetch(window._crmRoutes.previewHtml, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ html_body: html }),
    })
    .then(function (r) { return r.text(); })
    .then(function (rendered) {
        win.document.open();
        win.document.write(rendered);
        win.document.close();
    })
    .catch(function () { win.document.body.innerHTML = 'Error loading preview.'; });
};
