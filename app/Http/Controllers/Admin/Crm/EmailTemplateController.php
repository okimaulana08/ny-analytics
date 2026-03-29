<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreEmailTemplateRequest;
use App\Http\Requests\Admin\Crm\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\BrevoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::withCount('campaigns')->orderByDesc('created_at')->get();

        return view('admin.crm.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.crm.templates.create');
    }

    public function store(StoreEmailTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        EmailTemplate::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'html_body' => $data['html_body'],
            'preview_text' => $data['preview_text'] ?? null,
            'is_active' => true,
            'created_by' => session('admin_id'),
        ]);

        return redirect()->route('admin.crm.templates.index')
            ->with('success', 'Template email berhasil dibuat.');
    }

    public function edit(EmailTemplate $template): View
    {
        return view('admin.crm.templates.edit', compact('template'));
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $template): RedirectResponse
    {
        $template->update($request->validated());

        return redirect()->route('admin.crm.templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.crm.templates.index')
            ->with('success', 'Template email berhasil dihapus.');
    }

    public function aiGenerate(Request $request): JsonResponse
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'API key Anthropic belum dikonfigurasi.'], 400);
        }

        $intent = trim($request->input('intent', ''));
        if (empty($intent)) {
            return response()->json(['error' => 'Deskripsi tujuan email wajib diisi.'], 422);
        }

        /** @var array<int, array{key: string, description: string}> $params */
        $params = $request->input('params', []);

        $paramLines = '';
        foreach ($params as $p) {
            $key = trim($p['key'] ?? '');
            $desc = trim($p['description'] ?? '');
            if ($key !== '') {
                $paramLines .= '- {{'.$key.'}}'.($desc !== '' ? " → {$desc}" : '')."\n";
            }
        }

        $paramSection = $paramLines !== ''
            ? "PARAMETER DINAMIS YANG HARUS ADA DI HTML:\n{$paramLines}"
            : 'PARAMETER DINAMIS: Tentukan sendiri parameter yang relevan sesuai tujuan email.';

        $appUrl = config('app.url');

        $prompt = <<<PROMPT
Kamu adalah desainer email HTML profesional untuk platform baca novel digital bernama **Novelya** (novelya.id).
Buat template email HTML yang lengkap, responsif, dan menarik — HARUS kompatibel dengan Gmail, Outlook, dan Apple Mail.

TUJUAN EMAIL:
{$intent}

{$paramSection}

ATURAN WAJIB (EMAIL CLIENT COMPATIBILITY):
1. Gunakan merge tag format {{nama_parameter}} (dua kurung kurawal) untuk nilai dinamis. Contoh: {{name}}, {{email}}, {{story_title}}.
2. DILARANG: display:flex, display:grid, CSS transitions, CSS animations, -webkit-line-clamp. Gmail tidak mendukungnya.
3. Untuk layout multi-kolom atau centering elemen: WAJIB gunakan <table cellpadding="0" cellspacing="0">.
4. Untuk gambar cerita (story_cover): gunakan <img> di dalam <td style="text-align:center"> dengan width="200" style="display:block;margin:0 auto;max-width:100%;height:auto;border-radius:8px".
5. Semua CSS properti penting harus ada sebagai INLINE STYLE (bukan hanya di <style> tag), terutama pada <img>, <a>, <td>, dan elemen kunci.
6. Warna brand Novelya: primary #7c3aed (violet), secondary #a78bfa (light violet), background putih/abu muda.
7. Sertakan logo/nama "Novelya" di header dengan background gradient violet dan link {$appUrl} di footer.
8. Tone: hangat, personal, dalam Bahasa Indonesia.
9. Jangan gunakan JavaScript.
10. Max-width wrapper: 600px, menggunakan <table width="100%"> sebagai outer wrapper.

OUTPUT WAJIB — balas HANYA dengan JSON object berikut (tanpa markdown code fence, tanpa teks lain):
{
  "subject": "subject email yang menarik",
  "preview_text": "teks preview inbox maksimal 90 karakter",
  "html_body": "HTML lengkap di sini"
}
PROMPT;

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 4096,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

            if (! $response->successful()) {
                return response()->json(['error' => 'AI error: '.$response->status()], 500);
            }

            $text = $response->json('content.0.text', '');

            // Strip potential markdown fences
            $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
            $text = preg_replace('/\s*```$/', '', $text);

            $decoded = json_decode($text, true);

            if (! is_array($decoded) || empty($decoded['html_body'])) {
                return response()->json(['error' => 'Respons AI tidak valid. Coba lagi.', 'raw' => $text], 500);
            }

            return response()->json([
                'subject' => $decoded['subject'] ?? '',
                'preview_text' => $decoded['preview_text'] ?? '',
                'html_body' => $decoded['html_body'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi AI: '.$e->getMessage()], 500);
        }
    }

    public function previewHtml(Request $request): Response
    {
        $html = $request->input('html_body', '');
        $brevo = app(BrevoService::class);
        $rendered = $brevo->renderTemplate($html, [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'expiry_date' => now()->addDays(7)->format('d M Y'),
            'plan_name' => 'Premium Bulanan',
            'app_url' => config('brevo.novelya_url', config('app.url')),
            'story_title' => 'Cinta di Balik Hujan',
            'story_cover' => 'https://placehold.co/300x400/7c3aed/ffffff?text=Cover',
            'story_synopsis' => 'Sebuah kisah cinta yang mengharukan antara dua jiwa yang dipertemukan oleh takdir pada malam hujan yang tak terlupakan...',
            'story_url' => config('brevo.novelya_url', 'https://novelya.id').'/detail/demo',
            'invoice_url' => config('brevo.novelya_url', 'https://novelya.id').'/payment/demo',
            'payment_status' => 'pending',
            'join_date' => now()->subDays(30)->format('d M Y'),
            'last_paid' => now()->subDays(90)->format('d M Y'),
            'trx_count' => '3',
            'paid_at' => now()->subDays(1)->format('d M Y H:i'),
        ]);

        return response($rendered, 200, ['Content-Type' => 'text/html']);
    }

    public function preview(EmailTemplate $template): Response
    {
        $brevo = app(BrevoService::class);
        $rendered = $brevo->renderTemplate($template->html_body, [
            'name' => 'Pengguna Demo',
            'email' => 'demo@novelya.id',
            'expiry_date' => now()->addDays(7)->format('d M Y'),
            'plan_name' => 'Premium Bulanan',
            'app_url' => config('app.url'),
        ]);

        return response($rendered, 200, ['Content-Type' => 'text/html']);
    }
}
