<?php

namespace Molitor\Scraper\Filament\Resources\ScraperResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Scraper\Filament\Resources\ScraperResource;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Services\ScraperService;
use Molitor\Scraper\Services\Url;

class CreateScraper extends CreateRecord
{
    protected static string $resource = ScraperResource::class;

    public function getTitle(): string
    {
        return __('scraper::messages.scraper.pages.create');
    }

    public function afterCreate()
    {
        /** @var Scraper $scraper */
        $scraper = $this->record;

        /** @var ScraperService $scraperService */
        $scraperService = app(ScraperService::class);
        $scraperService->updateBaseLinks($scraper);
    }

    protected function getRedirectUrl(): string
    {
        /** @var Scraper $scraper */
        $scraper = $this->record;

        return ScraperUrlResource::getUrl('index', [
            'scraper_id' => $scraper->id,
        ]);
    }
}
