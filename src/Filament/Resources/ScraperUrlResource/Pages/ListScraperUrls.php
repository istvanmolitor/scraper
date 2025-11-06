<?php

namespace Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource;

class ListScraperUrls extends ListRecords
{
    protected static string $resource = ScraperUrlResource::class;

    public function getTitle(): string
    {
        return 'Scraper URLs';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create URL')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return ScraperUrlResource::table($table);
    }
}
