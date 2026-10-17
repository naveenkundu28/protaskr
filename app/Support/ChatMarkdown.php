<?php

namespace App\Support;

/**
 * Turns a plain-text chat message into safe HTML.
 *
 * Supported: **bold**, *italic* (or _italic_), __underline__, bullet lists
 * ("- item"), numbered lists ("1. item"), line breaks and links. Everything
 * else, including any HTML the user types, is shown as plain text.
 */
class ChatMarkdown
{
    public static function toHtml(string $text): string
    {
        $lines = preg_split('/\R/u', trim($text));

        $html = '';
        $paragraph = [];
        $list = null; // 'ul', 'ol' or null

        $closeParagraph = function () use (&$html, &$paragraph) {
            if ($paragraph) {
                $html .= '<p>' . implode('<br>', $paragraph) . '</p>';
                $paragraph = [];
            }
        };
        $closeList = function () use (&$html, &$list) {
            if ($list) {
                $html .= "</$list>";
                $list = null;
            }
        };

        foreach ($lines as $line) {
            $line = rtrim($line);

            if (preg_match('/^\s*[-*]\s+(.+)$/u', $line, $m)) {
                $type = 'ul';
            } elseif (preg_match('/^\s*\d+[.)]\s+(.+)$/u', $line, $m)) {
                $type = 'ol';
            } else {
                $type = null;
            }

            if ($type) {
                $closeParagraph();
                if ($list !== $type) {
                    $closeList();
                    $html .= "<$type>";
                    $list = $type;
                }
                $html .= '<li>' . self::inline($m[1]) . '</li>';

                continue;
            }

            $closeList();

            if (trim($line) === '') {
                $closeParagraph();
            } else {
                $paragraph[] = self::inline($line);
            }
        }

        $closeParagraph();
        $closeList();

        return $html;
    }

    private static function inline(string $text): string
    {
        // escape first, so nothing typed by the user can become markup
        $text = e($text);

        $text = preg_replace('/\*\*(?=\S)(.+?)(?<=\S)\*\*/u', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(?=\S)(.+?)(?<=\S)__/u', '<u>$1</u>', $text);
        $text = preg_replace('/(?<![\w*])\*(?=\S)(.+?)(?<=\S)\*(?![\w*])/u', '<em>$1</em>', $text);
        $text = preg_replace('/(?<![\w_])_(?=\S)(.+?)(?<=\S)_(?![\w_])/u', '<em>$1</em>', $text);

        // plain http(s) addresses become links
        return preg_replace(
            '~(?<![">])\bhttps?://[^\s<]+[^\s<.,;:!?)\]]~u',
            '<a href="$0" target="_blank" rel="noopener noreferrer">$0</a>',
            $text
        );
    }
}
