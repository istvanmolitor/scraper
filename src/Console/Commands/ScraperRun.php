<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Repositories\ScraperUrlRepository;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;
use Molitor\Scraper\Services\ScraperService;

class ScraperRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tartalmak letöltése';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scraperRepository = app(ScraperRepositoryInterface::class);
        $scraper = $scraperRepository->getNextScraper();
        if(!$scraper) {
            return 0;
        }

        /** @var ScraperUrlRepository $scraperUrlRepository */
        $scraperUrlRepository = app(ScraperUrlRepositoryInterface::class);

        /** @var ScraperService $scraperService */
        $scraperService = app(ScraperService::class);

        //$scraperRepository->start($scraper);
        $scraperUrls = $scraperUrlRepository->getTasksByScraper($scraper);

        foreach ($scraperUrls as $scraperUrl) {
            $this->info('Download: ' . $scraperUrl->id . ' - '. $scraperUrl->url);
            $scraperService->downloadScraperUrl($scraperUrl);
        }

        $scraperRepository->stop($scraper);

        return 0;
    }
}
