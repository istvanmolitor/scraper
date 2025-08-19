<?php

namespace Molitor\Scraper\Providers;

use Illuminate\Support\ServiceProvider;
use Molitor\Scraper\Console\Commands\ScraperCreate;
use Molitor\Scraper\Console\Commands\ScraperDownload;
use Molitor\Scraper\Console\Commands\ScraperInfo;
use Molitor\Scraper\Console\Commands\ScraperRun;
use Molitor\Scraper\Repositories\ScraperRepository;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Repositories\ScraperUrlRepository;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;

class ScraperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/scraper.php' => config_path('scraper.php'),
        ], 'scraper');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScraperCreate::class,
                ScraperRun::class,
                ScraperDownload::class,
                ScraperInfo::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind(ScraperRepositoryInterface::class, ScraperRepository::class);
        $this->app->bind(ScraperUrlRepositoryInterface::class, ScraperUrlRepository::class);
    }
}
