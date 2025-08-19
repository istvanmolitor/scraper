<?php

namespace Molitor\Scraper\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperUrlUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ScraperUrl $scraperUrl;

    public ?array $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ScraperUrl $scraperUrl, ?array $data)
    {
        $this->scraperUrl = $scraperUrl;
        $this->data = $data;
    }
}
