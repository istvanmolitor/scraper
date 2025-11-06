<?php

namespace Molitor\Scraper\Filament\Resources\ScraperResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Scraper\Filament\Resources\ScraperResource;

class ListScrapers extends ListRecords
{
    protected static string $resource = ScraperResource::class;

    public function getTitle(): string
    {
        return 'Scrapers';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Scraper')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return ScraperResource::table($table);
    }
}
