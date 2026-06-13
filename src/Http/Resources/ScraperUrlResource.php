<?php

namespace Molitor\Scraper\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScraperUrlResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isReady = $this->downloaded_at !== null
            && ($this->expiration_at === null || $this->expiration_at->isFuture());

        return [
            'id' => $this->id,
            'scraper_id' => $this->scraper_id,
            'type' => $this->type,
            'url' => $this->url,
            'priority' => $this->priority,
            'parent_id' => $this->parent_id,
            'downloaded_at' => $this->downloaded_at?->toDateTimeString(),
            'expiration_at' => $this->expiration_at?->toDateTimeString(),
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'ready' => $isReady,
            'scraper' => $this->whenLoaded('scraper', function (): array {
                return [
                    'id' => $this->scraper->id,
                    'name' => $this->scraper->name,
                    'base_url' => $this->scraper->base_url,
                ];
            }),
        ];
    }
}
