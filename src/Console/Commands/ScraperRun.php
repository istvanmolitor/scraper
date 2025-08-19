<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Scraper\Services\ScraperService;

class ScraperRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:run {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Weboldalak szkennelése';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scraper = app(ScraperService::class);
        $scraper->setCommand($this);
        $scraper->run();

        return 0;
    }
}
