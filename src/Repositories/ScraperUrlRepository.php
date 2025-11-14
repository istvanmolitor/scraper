<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperUrlRepository implements ScraperUrlRepositoryInterface
{
    protected ScraperUrl $scraperUrl;

    public function __construct()
    {
        $this->scraperUrl = new ScraperUrl();
    }

    public function getById(int $id): ?ScraperUrl
    {
        return $this->scraperUrl->where('id', $id)->first();
    }

    public function getScraperUrl(Scraper $scraper, string $link): ?ScraperUrl
    {
        return $this->scraperUrl->where('scraper_id', $scraper->id)
            ->where('hash', md5($link))
            ->where('url', $link)
            ->first();
    }

    public function getNextScraperUrls(Scraper $scraper, int $limit): Collection
    {
        return $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->where(function ($query) {
                $query->whereNull('expiration_at')
                    ->orWhere('expiration_at', '<=', Carbon::now())
                    ->orWhereNull('downloaded_at');
            })
            ->orderBy('priority', 'ASC')
            ->orderBy('downloaded_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * URL-ek tömeges beszúrása
     * @return void
     */
    public function storeUrls(array $rows): int
    {
        return $this->scraperUrl->insertOrIgnore($rows);
    }

    public function getCursor(Scraper $scraper): LazyCollection
    {
        return $this->scraperUrl->where('scraper_id', $scraper->id)->orderBy('url')->cursor();
    }

    public function deleteByScraper(Scraper $scraper): void
    {
        $this->scraperUrl->where('scraper_id', $scraper->id)->delete();
    }

    public function getNumAllUrlsByScraper(Scraper $scraper): int
    {
        return $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->count();
    }

    public function getNumTaskUrlsByScraper(Scraper $scraper): int
    {
        return $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->where(function ($query) {
                $query->whereNull('expiration_at')
                    ->orWhere('expiration_at', '<=', Carbon::now())
                    ->orWhereNull('downloaded_at');
            })
            ->count();
    }

    public function getNumDownloadUrlsByScraper(Scraper $scraper): int
    {
        return $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->whereNotNull('downloaded_at')
            ->count();
    }

    /**
     * Visszaadja az url-ek számát amit letöltött az elmúlt időszakban
     * @param Scraper $scraper
     * @return int
     */
    private function getNumTasksByScraper(Scraper $scraper): int
    {
        if (!$scraper->enabled) {
            return $scraper->period_limit;
        }

        $periodStart = CarbonImmutable::now()->addSeconds($scraper->period_length * -1);

        return $this->scraperUrl->where('scraper_id', $scraper->id)
            ->whereNotNull('downloaded_at')
            ->where('downloaded_at', '>=', $periodStart)
            ->count();
    }

    public function getTasksByScraper(Scraper $scraper): LazyCollection
    {
        return $this->scraperUrl
            ->where(function ($query) use ($scraper) {
                $query->where('scraper_id', $scraper->id)
                    ->orWhere('expiration_at', '<=', Carbon::now());
            })
            ->orderBy('priority', 'ASC')
            ->orderBy('downloaded_at', 'DESC')
            ->limit($scraper->chunk_size)
            ->cursor();
    }

    public function save(Scraper|ScraperUrl $parent, array $data): ScraperUrl
    {
        if($parent instanceof Scraper) {
            $data['scraper_id'] = $parent->id;
        }
        else {
            $data['scraper_id'] = $parent->scraper_id;
            $data['parent_id'] = $parent->id;
        }
        return $this->scraperUrl->create($data);
    }

    // Global aggregations
    public function getNumAllUrls(): int
    {
        return $this->scraperUrl->count();
    }

    public function getNumDownloadedUrls(): int
    {
        return $this->scraperUrl->whereNotNull('downloaded_at')->count();
    }

    public function getNumFreshUrls(): int
    {
        return $this->scraperUrl
            ->whereNotNull('downloaded_at')
            ->where(function ($q) {
                $q->whereNull('expiration_at')
                    ->orWhere('expiration_at', '>', Carbon::now());
            })
            ->count();
    }
}
