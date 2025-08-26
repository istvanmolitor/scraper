<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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

    public function getNextScraper(): ?Scraper
    {
        return $this->scraper->where('enabled', 1)
            ->where(function ($query) {
                $query->whereNull('blocked')
                    ->orWhere('blocked', '<', now());
            })
            ->first();
    }

    public function start(Scraper $scraper): Carbon
    {
        $blocked = Carbon::now();
        $blocked->addSeconds($scraper->chunk_size * 10);
        $scraper->blocked = $blocked;
        $scraper->save();
        return $blocked;
    }

    public function stop(Scraper $scraper): void
    {
        $scraper->blocked = null;
        $scraper->save();
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
