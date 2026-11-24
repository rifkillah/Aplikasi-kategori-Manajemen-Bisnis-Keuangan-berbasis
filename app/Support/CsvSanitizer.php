<?php

namespace App\Support;

class CsvSanitizer
{
    /**
     * Neutralise spreadsheet formula injection (CSV injection).
     *
     * Excel, LibreOffice, and Google Sheets treat cells beginning with one of
     * these characters as formulas. Prefixing an apostrophe keeps the value
     * visible as text instead of executing it when the report is opened.
     */
    public static function cell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = str_replace("\0", '', $value);

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
