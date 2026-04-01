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
                if (data.html_body) {
                    document.getElementById('field_html_body').value = data.html_body;
                    if (window.cmEditor) {
                        window.cmEditor.setValue(data.html_body);
                    }
                }
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
    var type = window._crmCurrentType || 'custom';
    var win = window.open('', '_blank');
    win.document.write('Memuat preview...');

    var payload;
    if (type !== 'custom') {
        payload = JSON.stringify({
            template_type: type,
            template_settings: window._crmTemplateSettings || {},
        });
    } else {
        var html = document.getElementById('field_html_body').value;
        if (!html.trim()) {
            win.close();
            alert('HTML body kosong.');
            return;
        }
        payload = JSON.stringify({ html_body: html });
    }

    fetch(window._crmRoutes.previewHtml, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: payload,
    })
    .then(function (r) { return r.text(); })
    .then(function (rendered) {
        win.document.open();
        win.document.write(rendered);
        win.document.close();
    })
    .catch(function () { win.document.body.innerHTML = 'Error loading preview.'; });
};

// --- CodeMirror + live preview ---
window.cmEditor       = null;
var previewOpen       = false;
var previewTimer      = null;
var isSyncingFromPreview = false;

document.addEventListener('DOMContentLoaded', function () {
    var textarea = document.getElementById('field_html_body');
    var mount    = document.getElementById('cm-editor-mount');

    if (textarea && mount && typeof CodeMirror !== 'undefined') {
        // Store original html_body value so we can restore it if user switches back to custom
        textarea.setAttribute('data-orig', textarea.value);

        var isDark       = document.documentElement.classList.contains('dark');
        var initialType  = window._crmCurrentType || 'custom';
        var isBuiltIn    = initialType !== 'custom';

        window.cmEditor = CodeMirror(mount, {
            value: isBuiltIn ? '' : textarea.value,
            mode: 'htmlmixed',
            theme: isDark ? 'dracula' : 'default',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 2,
            tabSize: 2,
            autofocus: false,
            readOnly: isBuiltIn,
        });

        window.cmEditor.on('change', function () {
            if (isBuiltIn) { return; }
            textarea.value = window.cmEditor.getValue();
            if (!isSyncingFromPreview) {
                schedulePreviewRefresh();
            }
        });

        setTimeout(function () { window.cmEditor.refresh(); }, 50);

        // Load sample HTML for built-in types on page load
        if (isBuiltIn) {
            loadBuiltInSampleHtml(initialType, window._crmTemplateSettings || {});
        }
    }

    initTogglePreview();
});

// Called from Alpine when templateType changes (create mode only)
window._onTemplateTypeChange = function (newType) {
    var cm = window.cmEditor;
    if (!cm) { return; }

    var textarea = document.getElementById('field_html_body');

    if (newType === 'custom') {
        cm.setOption('readOnly', false);
        var orig = (textarea && textarea.getAttribute('data-orig')) || '';
        cm.setValue(orig);
        if (textarea) { textarea.value = orig; }
    } else {
        cm.setOption('readOnly', true);
        loadBuiltInSampleHtml(newType, window._crmTemplateSettings || {});
    }

    if (previewOpen) {
        schedulePreviewRefresh();
    }
};

// Called from Alpine when promo settings change
window._refreshBuiltInPreview = function () {
    var type = window._crmCurrentType || 'custom';
    if (type === 'custom') { return; }
    loadBuiltInSampleHtml(type, window._crmTemplateSettings || {});
};

function loadBuiltInSampleHtml(type, settings) {
    fetch(window._crmRoutes.previewHtml, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ template_type: type, template_settings: settings }),
    })
    .then(function (r) { return r.text(); })
    .then(function (html) {
        if (window.cmEditor) {
            window.cmEditor.setValue(html);
        }
        var textarea = document.getElementById('field_html_body');
        if (textarea) { textarea.value = html; }
        if (previewOpen) {
            var iframeEl = document.getElementById('preview-iframe');
            if (iframeEl) { iframeEl.srcdoc = html; }
        }
    });
}

function schedulePreviewRefresh() {
    if (!previewOpen) { return; }
    clearTimeout(previewTimer);
    previewTimer = setTimeout(refreshPreview, 500);
}

function refreshPreview() {
    var type    = window._crmCurrentType || 'custom';
    var payload;

    if (type !== 'custom') {
        payload = JSON.stringify({
            template_type: type,
            template_settings: window._crmTemplateSettings || {},
        });
    } else {
        var html = document.getElementById('field_html_body').value;
        if (!html.trim()) { return; }
        payload = JSON.stringify({ html_body: html });
    }

    var loadingEl = document.getElementById('preview-loading');
    var iframeEl  = document.getElementById('preview-iframe');
    if (loadingEl) {
        loadingEl.classList.remove('hidden');
        loadingEl.classList.add('flex');
    }

    fetch(window._crmRoutes.previewHtml, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: payload,
    })
    .then(function (r) { return r.text(); })
    .then(function (rendered) {
        if (loadingEl) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
        }
        iframeEl.onload = function () {
            makePreviewEditable(iframeEl);
        };
        iframeEl.srcdoc = rendered;
    })
    .catch(function () {
        if (loadingEl) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
        }
    });
}

function makePreviewEditable(iframeEl) {
    var doc = iframeEl.contentDocument;
    if (!doc || !doc.body) { return; }

    var type = window._crmCurrentType || 'custom';

    // Direct editing only for custom type
    if (type === 'custom') {
        doc.body.contentEditable = 'true';
        doc.body.style.outline = 'none';

        // Hint toast
        var hint = doc.createElement('div');
        hint.style.cssText = 'position:fixed;bottom:10px;right:10px;background:rgba(124,58,237,0.9);color:#fff;font-size:11px;padding:5px 12px;border-radius:6px;z-index:9999;pointer-events:none;font-family:sans-serif;opacity:1;transition:opacity 0.5s;';
        hint.textContent = '\u270F\uFE0F Klik untuk edit langsung';
        doc.body.appendChild(hint);
        setTimeout(function () { hint.style.opacity = '0'; }, 2500);
        setTimeout(function () { if (hint.parentNode) { hint.parentNode.removeChild(hint); } }, 3100);

        // MutationObserver → sync ke CodeMirror
        var syncTimer = null;
        var observer  = new MutationObserver(function () {
            clearTimeout(syncTimer);
            syncTimer = setTimeout(function () {
                var html = doc.documentElement.outerHTML;
                isSyncingFromPreview = true;
                document.getElementById('field_html_body').value = html;
                if (window.cmEditor) {
                    window.cmEditor.setValue(html);
                }
                isSyncingFromPreview = false;
            }, 800);
        });
        observer.observe(doc.body, { childList: true, subtree: true, characterData: true });
    }

    // Click → highlight baris di CodeMirror (semua tipe)
    doc.addEventListener('click', function (e) {
        var el = e.target;
        while (el && (el.tagName === 'BODY' || el.tagName === 'HTML')) {
            el = el.parentElement;
        }
        if (!el || !window.cmEditor) { return; }

        var tagName  = el.tagName.toLowerCase();
        var textSnip = el.textContent.trim().replace(/\s+/g, ' ').substring(0, 40);
        var source   = window.cmEditor.getValue();
        var lines    = source.split('\n');

        var targetLine = -1;
        for (var i = 0; i < lines.length; i++) {
            if (lines[i].toLowerCase().indexOf('<' + tagName) !== -1) {
                var context = lines.slice(i, Math.min(i + 6, lines.length)).join('\n');
                if (textSnip && context.indexOf(textSnip) !== -1) {
                    targetLine = i;
                    break;
                }
            }
        }
        if (targetLine === -1) {
            for (var j = 0; j < lines.length; j++) {
                if (lines[j].toLowerCase().indexOf('<' + tagName) !== -1) {
                    targetLine = j;
                    break;
                }
            }
        }

        if (targetLine >= 0) {
            window.cmEditor.scrollIntoView({ line: targetLine, ch: 0 }, 100);
            window.cmEditor.setCursor({ line: targetLine, ch: 0 });
            window.cmEditor.addLineClass(targetLine, 'background', 'cm-highlight-line');
            setTimeout(function () {
                window.cmEditor.removeLineClass(targetLine, 'background', 'cm-highlight-line');
            }, 1500);
        }
    }, true);
}

function initTogglePreview() {
    var btn  = document.getElementById('btn-toggle-preview');
    var pane = document.getElementById('preview-pane');
    if (!btn || !pane) { return; }

    btn.addEventListener('click', function () {
        previewOpen = !previewOpen;
        pane.classList.toggle('hidden', !previewOpen);

        var iconSvg = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        btn.innerHTML = iconSvg + (previewOpen ? ' Sembunyikan' : ' Live Preview');

        if (previewOpen) {
            refreshPreview();
        }
    });
}
