<?php

namespace Tests\Unit;

use App\Support\CsvSanitizer;
use PHPUnit\Framework\TestCase;

class CsvSanitizerTest extends TestCase
{
    public function test_formula_prefixes_are_neutralised(): void
    {
        foreach (['=1+1', '+SUM(A1)', '-2+3', '@import', "\tcmd", "\rcmd"] as $value) {
            $this->assertSame("'{$value}", CsvSanitizer::cell($value));
        }
    }

    public function test_safe_values_are_untouched(): void
    {
        $this->assertSame('Kopi Susu', CsvSanitizer::cell('Kopi Susu'));
        $this->assertSame('', CsvSanitizer::cell(''));
        $this->assertSame(12500, CsvSanitizer::cell(12500));
        $this->assertSame(12.5, CsvSanitizer::cell(12.5));
        $this->assertNull(CsvSanitizer::cell(null));
    }

    public function test_null_bytes_are_removed(): void
    {
        $this->assertSame('abc', CsvSanitizer::cell("a\0bc"));
    }
}
