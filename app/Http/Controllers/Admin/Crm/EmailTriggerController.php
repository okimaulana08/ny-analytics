<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreEmailTriggerRequest;
use App\Http\Requests\Admin\Crm\UpdateEmailTriggerRequest;
use App\Models\EmailTemplate;
use App\Models\EmailTrigger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTriggerController extends Controller
{
    public function index(): View
    {
        $triggers = EmailTrigger::with('template')
            ->withCount('logs')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.crm.triggers.index', compact('triggers'));
    }

    public function create(): View
    {
        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.crm.triggers.create', compact('templates'));
    }

    public function store(StoreEmailTriggerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        EmailTrigger::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'],
            'email_template_id' => $data['email_template_id'],
            'conditions' => $this->buildConditions($data),
            'cooldown_days' => $data['cooldown_days'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil dibuat.');
    }

    public function edit(EmailTrigger $trigger): View
    {
        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.crm.triggers.edit', compact('trigger', 'templates'));
    }

    public function update(UpdateEmailTriggerRequest $request, EmailTrigger $trigger): RedirectResponse
    {
        $data = $request->validated();

        $trigger->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'],
            'email_template_id' => $data['email_template_id'],
            'conditions' => $this->buildConditions($data),
            'cooldown_days' => $data['cooldown_days'],
        ]);

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil diperbarui.');
    }

    public function toggle(EmailTrigger $trigger): RedirectResponse
    {
        $trigger->update(['is_active' => ! $trigger->is_active]);

        $status = $trigger->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Trigger \"{$trigger->name}\" berhasil {$status}.");
    }

    public function destroy(EmailTrigger $trigger): RedirectResponse
    {
        $trigger->delete();

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function buildConditions(array $data): ?array
    {
        return match ($data['trigger_type']) {
            EmailTrigger::TYPE_EXPIRY_REMINDER => ['days_before' => (int) ($data['days_before'] ?? 7)],
            EmailTrigger::TYPE_RE_ENGAGEMENT => ['inactive_days' => (int) ($data['inactive_days'] ?? 7)],
            default => null,
        };
    }
}
