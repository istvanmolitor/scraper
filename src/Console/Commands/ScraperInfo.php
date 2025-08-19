<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Services\ScraperService;

class ScraperInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraper információk';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scraperRepository = app(ScraperRepositoryInterface::class);

        $data = [];
        foreach ($scraperRepository->getAll() as $scraper) {
            $data[] = [
                $scraper->name,
                $scraper->base_url,
                $scraper->enabled ? 'Igen' : 'Nem',
                $scraper->follow_links ? 'Igen' : 'Nem',
                $scraper->robots_txt ? 'Igen' : 'Nem',
            ];
        }
        $this->table(['Név', 'Domain', 'Engedélyez', 'Linkek követése', 'Sitemap'], $data);

        return 0;
    }
}
