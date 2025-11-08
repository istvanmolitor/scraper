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

    public int|null $scraperId = null;

    public function getTitle(): string
    {
        return __('scraper::messages.scraper_url.pages.title');
    }

    public function mount(): void
    {
        parent::mount();
        $this->scraperId = request()->integer('scraper_id');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('scraper::messages.scraper_url.pages.create'))
                ->icon('heroicon-o-plus')
                ->url(function () {
                    $scraperId = request()->integer('scraper_id');
                    return ScraperUrlResource::getUrl('create', $scraperId ? ['scraper_id' => $scraperId] : []);
                }),
            \Filament\Actions\Action::make('export')
                ->label(__('scraper::messages.scraper_url.pages.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->url(function () {
                    $scraperId = request()->integer('scraper_id');
                    $params = $scraperId ? ['scraper_id' => $scraperId] : [];
                    return route('scraper.scraper_urls.export', $params);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = parent::getTableQuery();
        $scraperId = request()->integer('scraper_id');
        if ($scraperId > 0) {
            $query->where('scraper_id', $scraperId);
        }
        return $query;
    }

    public function table(Table $table): Table
    {
        $table = ScraperUrlResource::table($table);
        if ($this->scraperId) {
            $table->modifyQueryUsing(function ($query) {
                $query->where('scraper_id', $this->scraperId);
            });
        }

        return $table;
    }
}
