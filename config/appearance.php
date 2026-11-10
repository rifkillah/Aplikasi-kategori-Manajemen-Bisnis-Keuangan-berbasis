<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Appearance presets
    |--------------------------------------------------------------------------
    |
    | Every preset maps to a `data-bg` value on <html> and is styled in
    | resources/css/app.css. The swatch is only used by the settings page.
    |
    */

    'presets' => [
        'aurora' => [
            'label' => 'Aurora',
            'description' => 'Indigo, violet, dan biru lembut',
            'swatch' => 'linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #38bdf8 100%)',
        ],
        'sunset' => [
            'label' => 'Senja',
            'description' => 'Oranye, rose, dan amber hangat',
            'swatch' => 'linear-gradient(135deg, #fb923c 0%, #f472b6 50%, #fbbf24 100%)',
        ],
        'ocean' => [
            'label' => 'Samudra',
            'description' => 'Biru, sian, dan teal segar',
            'swatch' => 'linear-gradient(135deg, #38bdf8 0%, #22d3ee 50%, #2dd4bf 100%)',
        ],
        'forest' => [
            'label' => 'Hutan',
            'description' => 'Hijau, lime, dan emerald',
            'swatch' => 'linear-gradient(135deg, #34d399 0%, #a3e635 50%, #14b8a6 100%)',
        ],
        'graphite' => [
            'label' => 'Grafit',
            'description' => 'Netral, tenang, dan profesional',
            'swatch' => 'linear-gradient(135deg, #94a3b8 0%, #cbd5e1 50%, #64748b 100%)',
        ],
        'plain' => [
            'label' => 'Minimal',
            'description' => 'Warna solid tanpa gradasi',
            'swatch' => 'linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%)',
        ],
    ],

    'modes' => [
        'system' => 'Ikuti sistem',
        'light' => 'Terang',
        'dark' => 'Gelap',
    ],

];
