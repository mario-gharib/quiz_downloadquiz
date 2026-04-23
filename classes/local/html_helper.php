<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * HTML helper utilities for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper methods for safe HTML and text handling.
 */
final class html_helper {

    /**
     * Convert a nullable or mixed scalar value to string safely.
     *
     * Non-scalar values are normalised to an empty string.
     *
     * @param mixed $value
     * @return string
     */
    public static function safe_string($value): string {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Convert Moodle HTML into simpler PDF-safe HTML.
     *
     * The method removes script and style content, strips interactive form
     * controls and images, and normalises some block-level tags to keep the
     * generated PDF content stable and predictable.
     *
     * @param mixed $html
     * @return string
     */
    public static function normalise_for_pdf($html): string {
        $html = trim(self::safe_string($html));

        if ($html === '') {
            return '';
        }

        $html = preg_replace('~<script\b[^>]*>.*?</script>~is', '', $html);
        $html = preg_replace('~<style\b[^>]*>.*?</style>~is', '', $html);

        $html = preg_replace(
            [
                '~<(div|section|article|aside|header|footer)\b[^>]*>~i',
                '~</(div|section|article|aside|header|footer)>~i',
                '~<span\b[^>]*>~i',
                '~</span>~i',
            ],
            [
                '<div>',
                '</div>',
                '',
                '',
            ],
            $html
        );

        $html = preg_replace(
            '~<(input|select|option|textarea|button)\b[^>]*>.*?</\1>~is',
            '',
            $html
        );

        $html = preg_replace('~<(input|img)\b[^>]*?/?>~is', '', $html);
        $html = preg_replace('~<br\s*/?>~i', '<br />', $html);

        return self::safe_string($html);
    }

    /**
     * Escape plain text safely for HTML output.
     *
     * @param mixed $text
     * @return string
     */
    public static function escaped($text): string {
        return s(self::safe_string($text));
    }

    /**
     * Build a simple unordered list from preformatted items.
     *
     * Items are inserted as provided. Callers are responsible for escaping
     * plain-text values before passing them here.
     *
     * @param array $items
     * @return string
     */
    public static function unordered_list(array $items): string {
        if (empty($items)) {
            return '';
        }

        $html = '<ul>';

        foreach ($items as $item) {
            $html .= '<li>' . self::safe_string($item) . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}