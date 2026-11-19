<?php

namespace App\Support;

final class MarketingHtml
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><ol><ul><li><h1><h2><h3><blockquote><pre><code><a><div><span><hr><img><table><thead><tbody><tfoot><tr><th><td><caption><colgroup><col><figure><figcaption>';

    public static function sanitize(string $html): string
    {
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $html) ?? $html;
        $html = preg_replace_callback('/\s(src)\s*=\s*("|\')([^"\']*)("|\')/i', static function (array $match): string {
            $source = $match[3];
            $allowed = preg_match('/^(https?:|cid:|data:image\/(?:png|jpeg|gif|webp);base64,)/i', $source) === 1;

            return $allowed ? ' src="'.htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"' : ' src="#"';
        }, $html) ?? $html;
        $html = preg_replace_callback('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\')/i', static function (array $match): string {
            $style = trim($match[1], "\"'");
            $style = preg_replace('/(?:expression|javascript\s*:|vbscript\s*:|data\s*:\s*text\/html)/i', '', $style) ?? $style;

            return ' style="'.htmlspecialchars($style, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
        }, $html) ?? $html;

        return trim($html);
    }

    public static function render(string $content, bool $isHtml = false): string
    {
        if (! $isHtml) {
            return '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'.nl2br(e($content), false).'</div>';
        }

        return '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'.self::sanitize($content).'</div>';
    }

    public static function plainText(string $content, bool $isHtml = false): string
    {
        if (! $isHtml) {
            return trim($content);
        }

        return trim(html_entity_decode(strip_tags(self::sanitize($content)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
