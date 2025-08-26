<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Molitor\Scraper\Models\Scraper;

interface ScraperRepositoryInterface
{
    public function getNextScraper(): ?Scraper;

    public function start(Scraper $scraper): Carbon;

    public function stop(Scraper $scraper): void;

    public function getEnabledScrapers(): Collection;

    public function getAllForStatus(): Collection;

    public function getAll(): Collection;

    public function delete(Scraper $scraper): bool;

    public function create(string $name, string $baseUrl, bool $robotsTxt, bool $followLinks, bool $enabled): Scraper;

    public function getByName(string $name);
}
