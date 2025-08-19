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

        $html = <<<HTML
        <span class="rrr">
                <dl>

                    <dd>123</dd>
                    <dt>BBB</dt>
                    <dd>242</dd>
                    <dt>CCC</dt>
                    <dd>2423</dd>
                </dl>
            <div id="fff">
                <p>Első</p>
                <span>Második <b>Unoka</b></span>
                <span>Második <b>Unoka</b></span>
                <p>Harmadik</p>

            </div>
        </span>
        HTML;

        $a = new HtmlParser($html);

        dd($a->getDls());






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
        }
        else {
            $scraperUrl = $scraperService->getScraperUrlById($id);
        }

        $scraperService->downloadScraperUrl($scraperUrl);

        return 0;
    }
}
