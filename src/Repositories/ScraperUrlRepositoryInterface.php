<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Illuminate\Support\LazyCollection;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Models\ScraperUrl;

interface ScraperUrlRepositoryInterface
{
    public function save(Scraper|ScraperUrl $parent, array $data): ScraperUrl;

    public function getScraperUrl(Scraper $scraper, string $url): ?ScraperUrl;
    /**
     * URL-ek tömeges beszúrása
     * @return void
     */
    public function storeUrls(array $rows): int;

    public function getCursor(Scraper $scraper): LazyCollection;

    public function deleteByScraper(Scraper $scraper): void;

    public function getNumAllUrlsByScraper(Scraper $scraper): int;

    public function getNumTaskUrlsByScraper(Scraper $scraper): int;

    public function getNumDownloadUrlsByScraper(Scraper $scraper): int;

    public function getTasksByScraper(Scraper $scraper): LazyCollection;

    public function getById(int $id): ?ScraperUrl;
}
