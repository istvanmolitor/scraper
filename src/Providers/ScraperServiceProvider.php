<?php

namespace Molitor\Scraper\Providers;

use Illuminate\Support\ServiceProvider;
use Molitor\Scraper\Console\Commands\ScraperCreate;
use Molitor\Scraper\Console\Commands\ScraperDownload;
use Molitor\Scraper\Console\Commands\ScraperInfo;
use Molitor\Scraper\Console\Commands\ScraperRun;
use Molitor\Scraper\Console\Commands\ScraperWork;
use Molitor\Scraper\Repositories\ScraperRepository;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Repositories\ScraperUrlRepository;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;

class ScraperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'scraper');

        // Publish config (legacy tag maintained)
        $this->publishes([
            __DIR__ . '/../config/scraper.php' => config_path('scraper.php'),
        ], 'scraper');

        // Also allow publishing config with a dedicated tag
        $this->publishes([
            __DIR__ . '/../config/scraper.php' => config_path('scraper.php'),
        ], 'scraper-config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/scraper'),
        ], 'scraper-lang');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScraperCreate::class,
                ScraperRun::class,
                ScraperDownload::class,
                ScraperInfo::class,
                ScraperWork::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind(ScraperRepositoryInterface::class, ScraperRepository::class);
        $this->app->bind(ScraperUrlRepositoryInterface::class, ScraperUrlRepository::class);
    }
}
