<?php

namespace Molitor\Scraper\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Scraper\Exceptions\ScraperNameAlreadyExists;
use Molitor\Scraper\Services\ScraperService;

class ScraperCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Új scraper létrehozása';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->ask('Mi legyen a scraper neve?');
        $domain = $this->ask('Melyik domain-t szeretnéd scrape-elni?');
        $robotsTxt = $this->confirm('Kövesse a robots.txt-t?', true);
        $followLinks = $this->confirm('Kövesse a linkeket a scraper?', true);
        $enabled = $this->confirm('Szeretnéd engedélyezni?', true);

        $scraper = app(ScraperService::class);

        try {
            $scraper->createScraper(
                $name,
                $domain,
                $robotsTxt,
                $followLinks,
                $enabled
            );
        }
        catch (ScraperNameAlreadyExists $e) {
            $this->error("A név már foglalt.");
            return 1;
        }

        $this->info('A scraper sikeresen létrehozva!');

        return 0;
    }
}
