<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreScheduledReportRequest;
use App\Http\Requests\Admin\Crm\UpdateScheduledReportRequest;
use App\Models\ScheduledEmailReport;
use App\Services\BrevoService;
use App\Services\ScheduledReportBuilder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduledReportController extends Controller
{
    public function index(): View
    {
        $reports = ScheduledEmailReport::orderByDesc('created_at')->get();

        return view('admin.crm.scheduled-reports.index', compact('reports'));
    }

    public function create(): View
    {
        return view('admin.crm.scheduled-reports.create');
    }

    public function store(StoreScheduledReportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = session('admin_user.id');
        $data['next_run_at'] = $this->computeInitialNextRun($data);

        ScheduledEmailReport::create($data);

        return redirect()->route('admin.crm.scheduled-reports.index')
            ->with('success', 'Scheduled report berhasil dibuat.');
    }

    public function edit(ScheduledEmailReport $scheduledReport): View
    {
        return view('admin.crm.scheduled-reports.edit', compact('scheduledReport'));
    }

    public function update(UpdateScheduledReportRequest $request, ScheduledEmailReport $scheduledReport): RedirectResponse
    {
        $data = $request->validated();
        $data['next_run_at'] = $this->computeInitialNextRun($data);

        $scheduledReport->update($data);

        return redirect()->route('admin.crm.scheduled-reports.index')
            ->with('success', 'Scheduled report berhasil diperbarui.');
    }

    public function destroy(ScheduledEmailReport $scheduledReport): RedirectResponse
    {
        $scheduledReport->delete();

        return redirect()->route('admin.crm.scheduled-reports.index')
            ->with('success', 'Scheduled report berhasil dihapus.');
    }

    public function sendNow(ScheduledEmailReport $scheduledReport, BrevoService $brevo, ScheduledReportBuilder $builder): RedirectResponse
    {
        try {
            $html = $builder->build($scheduledReport);
            $subject = '[Novelya] '.$scheduledReport->name;

            $recipients = collect($scheduledReport->recipients)->map(fn ($r) => [
                'email' => $r['email'],
                'name' => $r['name'] ?? $r['email'],
                'params' => [],
            ])->all();

            $brevo->sendBatch($recipients, $subject, $html);

            $scheduledReport->update([
                'last_sent_at' => Carbon::now(),
                'next_run_at' => $scheduledReport->computeNextRun(),
            ]);

            return redirect()->route('admin.crm.scheduled-reports.index')
                ->with('success', "Report \"{$scheduledReport->name}\" berhasil dikirim.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.crm.scheduled-reports.index')
                ->with('error', 'Gagal mengirim: '.$e->getMessage());
        }
    }

    private function computeInitialNextRun(array $data): Carbon
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($data['frequency'] === ScheduledEmailReport::FREQ_WEEKLY) {
            $dow = (int) ($data['day_of_week'] ?? 1);
            $next = $today->copy();
            while ($next->dayOfWeek !== $dow) {
                $next->addDay();
            }
            if ($next->isToday()) {
                $next->addWeek();
            }

            return $next;
        }

        $dom = (int) ($data['day_of_month'] ?? 1);
        $next = $today->copy()->startOfMonth()->addDays($dom - 1);
        if ($next->isPast()) {
            $next->addMonthNoOverflow();
        }

        return $next;
    }
}
