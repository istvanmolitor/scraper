<?php

use Illuminate\Support\Facades\Route;
use Molitor\Scraper\Http\Controllers\ScraperUrlExportController;

Route::middleware(['web', 'auth'])
    ->prefix('scraper')
    ->name('scraper.')
    ->group(function () {
        Route::get('/scraper-urls/export', ScraperUrlExportController::class)
            ->name('scraper_urls.export');
    });
