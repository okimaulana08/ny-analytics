<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreEmailTemplateRequest;
use App\Http\Requests\Admin\Crm\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\BrevoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
