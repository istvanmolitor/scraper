<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\HtmlParser\HtmlParser;
use Molitor\Scraper\Services\ScraperService;

class ScraperWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:work {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Linkeke letöltése';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = $this->option('limit');
        if(! $limit) {
            $limit = 1000;
        }

        /** @var ScraperService $scraperService */
        $scraperService = app(ScraperService::class);

        $tasks = $scraperService->getTasks($limit);

        $progress = $this->output->createProgressBar($tasks->count());
        $progress->start();
        foreach ($tasks as $task) {
            $progress->advance();
            $scraperService->downloadScraperUrl($task);
        }

        $progress->finish();

        return 0;
    }
}
