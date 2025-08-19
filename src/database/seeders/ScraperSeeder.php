<?php

namespace Molitor\Scraper\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Scraper\Services\ScraperService;

class ScraperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app(ScraperService::class);
    }
}
