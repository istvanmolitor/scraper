<?php

namespace Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource;

class CreateScraperUrl extends CreateRecord
{
    protected static string $resource = ScraperUrlResource::class;

    public function getTitle(): string
    {
        return 'Create URL';
    }
}
