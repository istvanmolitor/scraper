<?php

namespace Molitor\Scraper\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\Scraper\Services\ScraperService;

class ScraperWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = app(ScraperService::class);
        if ($service->isEnabled()) {
            $service->run();
            ScraperWorker::dispatch()->delay(now()->addMinutes(1));
        }
    }
}

