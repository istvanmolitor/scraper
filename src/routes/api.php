<?php

use Illuminate\Support\Facades\Route;
use Molitor\Scraper\Http\Controllers\Api\ScraperApiController;
use Molitor\Scraper\Http\Controllers\Api\ScraperUrlApiController;
use Molitor\Scraper\Http\Controllers\ScraperUrlExportController;

Route::prefix('admin/scraper')
    ->middleware(['api', 'auth:sanctum', 'permission:scraper'])
    ->name('scraper.')
    ->group(function (): void {
        Route::get('scrapers/dashboard', [ScraperApiController::class, 'dashboard'])->name('scrapers.dashboard');
        Route::get('scrapers/create', [ScraperApiController::class, 'create'])->name('scrapers.create');
        Route::get('scrapers/{id}/edit', [ScraperApiController::class, 'edit'])->name('scrapers.edit');
        Route::resource('scrapers', ScraperApiController::class)->except(['create', 'edit']);

        Route::get('scraper-urls/create', [ScraperUrlApiController::class, 'create'])->name('scraper-urls.create');
        Route::get('scraper-urls/{id}/edit', [ScraperUrlApiController::class, 'edit'])->name('scraper-urls.edit');
        Route::post('scraper-urls/{id}/download', [ScraperUrlApiController::class, 'download'])->name('scraper-urls.download');
        Route::post('scraper-urls/bulk-download', [ScraperUrlApiController::class, 'bulkDownload'])->name('scraper-urls.bulk-download');
        Route::get('scraper-urls/export', ScraperUrlExportController::class)->name('scraper-urls.export');
        Route::resource('scraper-urls', ScraperUrlApiController::class)->except(['create', 'edit']);
    });