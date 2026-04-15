<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function db()
    {
        return DB::connection('novel');
    }

    public function index(): View
    {
        return view('admin.exports.index');
    }

    public function allUsers(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'csv');

        $users = $this->db()->select("
            SELECT
                u.name,
                u.email,
                p.phone_number,
                u.created_at AS registered_at,
                CASE
                    WHEN um.id IS NOT NULL AND um.is_active = 1 AND um.expired_at > NOW() THEN 'Member Aktif'
                    WHEN t_paid.user_id IS NOT NULL THEN 'Member Expired'
                    ELSE 'User Gratis'
                END AS status,
                CASE
                    WHEN mp.duration_days = 1 THEN 'Harian'
                    WHEN mp.duration_days = 7 THEN 'Mingguan'
                    WHEN mp.duration_days = 30 THEN 'Bulanan'
                    WHEN mp.duration_days = 365 THEN 'Tahunan'
                    WHEN mp.name IS NOT NULL THEN mp.name
                    ELSE '-'
                END AS plan_name
            FROM users u
            LEFT JOIN profile p ON p.user_id = u.id
            LEFT JOIN user_memberships um ON um.user_id = u.id AND um.is_active = 1
            LEFT JOIN membership_plans mp ON mp.id = um.plan_id
            LEFT JOIN (
                SELECT DISTINCT user_id FROM transactions WHERE status = 'paid'
            ) t_paid ON t_paid.user_id = u.id
            ORDER BY u.created_at DESC
        ");

        $filename = 'all-users-'.now()->format('Y-m-d_His');

        if ($format === 'csv') {
            return $this->streamCsv($users, $filename);
        }

        return $this->streamExcel($users, $filename);
    }

    private function streamCsv(array $rows, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, ['Nama', 'Email', 'Phone', 'Status', 'Plan Aktif/Terakhir', 'Tgl Registrasi']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->name,
                    $row->email,
                    $row->phone_number ?? '-',
                    $row->status,
                    $row->plan_name,
                    $row->registered_at,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function streamExcel(array $rows, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
        ];

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // Simple HTML table that Excel can open
            fwrite($handle, '<html><head><meta charset="UTF-8"></head><body>');
            fwrite($handle, '<table border="1">');
            fwrite($handle, '<tr><th>Nama</th><th>Email</th><th>Phone</th><th>Status</th><th>Plan Aktif/Terakhir</th><th>Tgl Registrasi</th></tr>');

            foreach ($rows as $row) {
                fwrite($handle, '<tr>');
                fwrite($handle, '<td>'.e($row->name).'</td>');
                fwrite($handle, '<td>'.e($row->email).'</td>');
                fwrite($handle, '<td>'.e($row->phone_number ?? '-').'</td>');
                fwrite($handle, '<td>'.e($row->status).'</td>');
                fwrite($handle, '<td>'.e($row->plan_name).'</td>');
                fwrite($handle, '<td>'.e($row->registered_at).'</td>');
                fwrite($handle, '</tr>');
            }

            fwrite($handle, '</table></body></html>');
            fclose($handle);
        }, 200, $headers);
    }
}
