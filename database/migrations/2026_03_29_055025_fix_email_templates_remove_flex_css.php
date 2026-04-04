<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove email-client-incompatible CSS from all email templates.
     * Gmail does not support display:flex, -webkit-line-clamp, etc.
     */
    public function up(): void
    {
        $templates = DB::table('email_templates')
            ->get(['id', 'html_body']);

        foreach ($templates as $template) {
            $html = $template->html_body ?? '';

            if (! str_contains($html, 'display:flex') && ! str_contains($html, 'display: flex')) {
                continue;
            }

            // Remove display:flex and related flex properties
            $html = preg_replace('/;?\s*display\s*:\s*flex\b/', '', $html) ?? $html;
            $html = preg_replace('/;?\s*align-items\s*:\s*[\w-]+/', '', $html) ?? $html;
            $html = preg_replace('/;?\s*justify-content\s*:\s*[\w-]+/', '', $html) ?? $html;

            // Remove -webkit-line-clamp (not supported in email clients)
            $html = preg_replace('/display\s*:\s*-webkit-box\s*;?/', '', $html) ?? $html;
            $html = preg_replace('/;?\s*-webkit-line-clamp\s*:\s*\d+/', '', $html) ?? $html;
            $html = preg_replace('/;?\s*-webkit-box-orient\s*:\s*\w+/', '', $html) ?? $html;

            // Fix story-image to be block-centered
            $html = preg_replace(
                '/class="story-image"\s+style="[^"]*"/i',
                'class="story-image" style="display:block;margin:0 auto;max-width:100%;height:auto;"',
                $html
            ) ?? $html;

            DB::table('email_templates')
                ->where('id', $template->id)
                ->update(['html_body' => $html]);
        }
    }

    public function down(): void {}
};
