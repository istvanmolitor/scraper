<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\HtmlParser\HtmlParser;
use Molitor\Scraper\Services\ScraperService;

class ScraperDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:download {link?} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link letöltése';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $link = $this->argument('link');
        $id = $this->option('id');

        if ($link && $id) {
            $this->error('Egyszerre csak linket VAGY ID-t adj meg.');
            return 1;
        }

        if (!$link && !$id) {
            $this->error('Adj meg linket (argumentum) vagy ID-t (--id=).');
            return 1;
        }

        /** @var ScraperService $scraperService */
        $scraperService = app(ScraperService::class);

        if($link) {
            $scraperUrl = $scraperService->getScraperUrlByLink($link);
            if(!$scraperUrl) {
                try {
                    $scraperUrl = $scraperService->storeLink($link);
                }
                catch (\Exception $e) {
                    $this->error($e->getMessage());
                    return 1;
                }
            }
        }
        else {
            $scraperUrl = $scraperService->getScraperUrlById($id);
        }

        $this->info('Download: ' . $scraperUrl->url);
        $scraperService->downloadScraperUrl($scraperUrl);

        return 0;
    }
}
