<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $amount, bool $withPrefix = true): string
    {
        $formatted = number_format((float) $amount, 0, ',', '.');

        return $withPrefix ? 'Rp '.$formatted : $formatted;
    }
}

if (! function_exists('public_storage_url')) {
    /**
     * Host-agnostic URL for a file on the public disk.
     *
     * Returns a path relative to the current origin so the browser always
     * requests the file from the same host that served the page. Absolute URLs
     * pinned to APP_URL break (and are blocked by CSP img-src 'self') when the
     * app is opened through 127.0.0.1, a LAN IP, www/non-www, or a temporary
     * domain.
     */
    function public_storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $relative = '/storage/'.ltrim($path, '/');
        $parts = parse_url(asset($relative));

        return $parts['path'] ?? $relative;
    }
}

if (! function_exists('role_label')) {
    function role_label(?string $role): string
    {
        return match ($role) {
            'owner' => 'Owner',
            'kasir' => 'Kasir',
            'gudang' => 'Staff Gudang',
            default => ucfirst((string) $role),
        };
    }
}

if (! function_exists('month_name')) {
    function month_name(int $month): string
    {
        return [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ][$month] ?? '';
    }
}

if (! function_exists('id_date')) {
    function id_date(?Carbon $date, bool $withTime = false): string
    {
        if (! $date) {
            return '-';
        }

        return $date->translatedFormat('d M Y').($withTime ? ' • '.$date->format('H:i') : '');
    }
}
