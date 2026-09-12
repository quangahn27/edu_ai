<?php

namespace App\Services\AI;

use App\Models\Lesson;
use Illuminate\Support\Str;

/**
 * Converts a lesson's CKEditor HTML into plain, structured text suitable
 * for inclusion in an AI prompt, dropping scripts/styles and markup noise.
 */
class LessonContextFormatter
{
    public function format(Lesson $lesson): string
    {
        $html = $lesson->content ?? '';
        $html = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6]|tr|blockquote)>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return Str::limit(trim($text), 4000);
    }
}
