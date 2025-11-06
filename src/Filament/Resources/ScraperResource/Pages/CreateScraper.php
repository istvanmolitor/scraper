<?php

namespace Molitor\Scraper\Filament\Resources\ScraperResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Scraper\Filament\Resources\ScraperResource;

class CreateScraper extends CreateRecord
{
    protected static string $resource = ScraperResource::class;

    public function getTitle(): string
    {
        return 'Create Scraper';
    }
}
