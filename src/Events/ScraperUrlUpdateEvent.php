<?php

namespace Molitor\Scraper\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperUrlUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public ScraperUrl $scraperUrl,
        public array $data
    )
    {

    }
}
