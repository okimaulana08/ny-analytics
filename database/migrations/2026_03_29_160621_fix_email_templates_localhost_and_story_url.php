<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace hardcoded http://localhost links with {{app_url}} merge tag,
     * and fix story URLs from /story/ to /detail/ in all email templates.
     */
    public function up(): void
    {
        $templates = DB::table('email_templates')
            ->get(['id', 'html_body']);

        foreach ($templates as $template) {
            $html = $template->html_body ?? '';
            $original = $html;

            // Replace hardcoded localhost with app_url merge tag
            $html = str_replace('http://localhost', '{{app_url}}', $html);
            $html = str_replace('https://localhost', '{{app_url}}', $html);

            // Fix story URL path: /story/ → /detail/
            $html = str_replace('/story/', '/detail/', $html);

            if ($html !== $original) {
                DB::table('email_templates')
                    ->where('id', $template->id)
                    ->update(['html_body' => $html]);
            }
        }
    }

    public function down(): void {}
};
