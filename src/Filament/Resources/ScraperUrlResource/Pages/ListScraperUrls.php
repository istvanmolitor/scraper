<?php

namespace Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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
                ->icon('heroicon-o-plus')
                ->url(function () {
                    $scraperId = request()->integer('scraper_id');
                    return ScraperUrlResource::getUrl('create', $scraperId ? ['scraper_id' => $scraperId] : []);
                }),
        ];
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = parent::getTableQuery();
        $scraperId = request()->integer('scraper_id');
        if ($scraperId) {
            $query->where('scraper_id', $scraperId);
        }
        return $query;
    }

    public function table(Table $table): Table
    {
        return ScraperUrlResource::table($table);
    }
}
