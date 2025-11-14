<?php

return [
    'navigation' => [
        'group_tools' => 'Eszközök',
        'scrapers' => 'Scraperek',
        'dashboard' => 'Vezérlőpult',
    ],

    'scraper' => [
        'fields' => [
            'enabled' => 'Engedélyezve',
            'name' => 'Név',
            'base_url' => 'Alap URL',
            'robots_txt' => 'Robots.txt figyelembevétele',
            'follow_links' => 'Hivatkozások követése',
            'chunk_size' => 'Csomagméret',
            'url' => 'Url',
        ],
        'table' => [
            'enabled' => 'Engedélyezve',
            'name' => 'Név',
            'base_url' => 'Alap URL',
            'urls_count' => 'URL-ek száma',
        ],
        'actions' => [
            'links' => 'Linkek',
        ],
        'pages' => [
            'title' => 'Scraperek',
            'create' => 'Scraper létrehozása',
            'edit' => 'Scraper szerkesztése',
        ],
    ],

    'scraper_url' => [
        'fields' => [
            'scraper' => 'Scraper',
            'type' => 'Típus',
            'priority' => 'Prioritás',
            'parent_id' => 'Szülő ID',
            'downloaded_at' => 'Letöltve ekkor',
            'expiration_at' => 'Lejárat',
            'meta_data' => 'Meta adatok',
            'meta_key' => 'Kulcs',
            'meta_value' => 'Érték',
            'ready' => 'Kész',
            'url' => 'URL',
            'expires' => 'Lejár',
        ],
        'pages' => [
            'title' => 'Scraper URL-ek',
            'create' => 'URL létrehozása',
            'edit' => 'URL szerkesztése',
            'export' => 'Lista letöltése',
        ],
        'bulk' => [
            'download' => 'Letöltés',
        ],
        'notifications' => [
            'download_started' => [
                'title' => 'Letöltés elindítva',
                'body' => 'A kiválasztott URL-ek letöltése folyamatban van.',
            ],
        ],
    ],

    'widgets' => [
        'total_links' => 'Összes link',
        'total_links_description' => 'Az összes URL a scraperben',
        'remaining_links' => 'Hátralévő letöltések',
        'remaining_links_description' => 'Még letöltendő URL-ek száma',
    ],
];
