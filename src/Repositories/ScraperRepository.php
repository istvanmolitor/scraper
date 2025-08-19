<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Scraper\Services\Url;
use Molitor\Scraper\Models\Scraper;

class ScraperRepository implements ScraperRepositoryInterface
{
    protected Scraper $scraper;
    private array $scrapers = [];

    public function __construct(
        protected ScraperUrlRepositoryInterface $scraperUrlRepository
    )
    {
        $this->scraper = new Scraper();
    }

    public function getByName(string $name): ?Scraper
    {
        return $this->scraper->where('name', $name)->first();
    }

    public function getEnabledScrapers(): Collection
    {
        return $this->scraper->where('enabled', 1)->get();
    }

    public function getAllForStatus(): Collection
    {
        return $this->scraper->orderBy('base_url')->withCount(['scraperUrls'])->get();
    }

    public function getAll(): Collection
    {
        return $this->scraper->orderBy('base_url')->get();
    }

    public function delete(Scraper $scraper): bool
    {
        $this->scraperUrlRepository->deleteByScraper($scraper);
        return $scraper->delete();
    }

    public function create(string $name, string $baseUrl, bool $robotsTxt, bool $followLinks, bool $enabled): Scraper
    {
        return $this->scraper->create([
            'name' => $name,
            'base_url' => $baseUrl,
            'enabled' => $enabled,
            'robots_txt' => $robotsTxt,
            'follow_links' => $followLinks,
        ]);
    }
}
