<?php

namespace Molitor\Scraper\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScraperResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isBlocked = $this->blocked !== null && $this->blocked->isFuture();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_url' => $this->base_url,
            'enabled' => (bool) $this->enabled,
            'robots_txt' => (bool) $this->robots_txt,
            'follow_links' => (bool) $this->follow_links,
            'chunk_size' => $this->chunk_size,
            'blocked' => $this->blocked?->toDateTimeString(),
            'is_blocked' => $isBlocked,
            'status' => ! $this->enabled ? 'inactive' : ($isBlocked ? 'blocked' : 'active'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'scraper_urls_count' => $this->whenCounted('scraperUrls'),
        ];
    }
}
