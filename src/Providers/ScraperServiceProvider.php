<?php

namespace Molitor\Scraper\Providers;

use Illuminate\Routing\Router;
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
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'scraper');

        $this->app->make(Router::class)
            ->prefix('api')
            ->group(__DIR__ . '/../routes/api.php');

        $this->app->make(Router::class)
            ->group([], __DIR__ . '/../routes/web.php');

        $this->publishes([
            __DIR__ . '/../config/scraper.php' => config_path('scraper.php'),
        ], 'config');

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

    public function register(): void
    {
        $this->app->bind(ScraperRepositoryInterface::class, ScraperRepository::class);
        $this->app->bind(ScraperUrlRepositoryInterface::class, ScraperUrlRepository::class);
    }
}
