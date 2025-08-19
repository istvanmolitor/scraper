<?php

declare(strict_types=1);

namespace Molitor\Scraper\Repositories;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
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

    public function getNextScraperUrl(Scraper $scraper, int $numberOfProcesses, int $processId): ?ScraperUrl
    {
        //Sor ami még soha nem volt letöltve
        $scraperUrl = $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->whereNull('downloaded_at')
            ->whereRaw('(id MOD ' . $numberOfProcesses . '=' . $processId . ')')
            ->orderBy('priority')
            ->first();

        if ($scraperUrl) {
            return $scraperUrl;
        }

        //Sor amit már le kell tölteni
        $scraperUrl = $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->where(function ($query) {
                $query->whereNull('expiration_at')
                    ->orWhere('expiration_at', '<=', Carbon::now());
            })
            ->whereRaw('(id MOD ' . $numberOfProcesses . '=' . $processId . ')')
            ->orderBy('priority')
            ->orderBy('downloaded_at')
            ->first();

        if ($scraperUrl) {
            return $scraperUrl;
        }

        if (!$scraper->follow_links) {
            return null;
        }

        if ($this->scraperUrl->where('scraper_id', $scraper->id)->count() === 0) {
            return $this->getScraperUrl($scraper, $scraper->base_url);
        }

        return null;
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

    public function getTasksByScraper(Scraper $scraper, int $limit): LazyCollection
    {
        $limit = min($limit, $scraper->period_limit - $this->getNumTasksByScraper($scraper));

        return $this->scraperUrl
            ->where('scraper_id', $scraper->id)
            ->orWhere('expiration_at', '<=', Carbon::now())
            ->orderBy('priority', 'ASC')
            ->orderBy('downloaded_at', 'DESC')
            ->limit($limit)
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

    public function getRobotsTxt(Scraper $scraper): ScraperUrl
    {
        $scraperUrl = $this->scraperUrl->where('scraper_id', $scraper->id)->where('type', 'robots')->find();
        if (!$scraperUrl) {
            $scraperUrl = $this->save($scraper, [
                'type' => 'robots',
                'url' => $scraper->base_url . '/robots.txt',
                'priority' => 0,
            ]);
        }
        return $scraperUrl;
    }
}
