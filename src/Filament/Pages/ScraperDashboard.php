<?php

namespace Molitor\Scraper\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Filament\Resources\ScraperResource;
use Molitor\Scraper\Filament\Widgets\ScraperLinksChart;
use Molitor\Scraper\Filament\Widgets\ScraperLinksWidget;

class ScraperDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-pie';

    public static function getNavigationGroup(): string
    {
        return __('scraper::messages.navigation.group_tools');
    }

    public static function getNavigationLabel(): string
    {
        return __('scraper::messages.navigation.scrapers');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    public function getTitle(): string|Htmlable
    {
        return __('scraper::messages.navigation.dashboard');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open-scrapers')
                ->label(__('scraper::messages.scraper.pages.title'))
                ->icon('heroicon-o-list-bullet')
                ->url(fn () => ScraperResource::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ScraperLinksChart::class,
            ScraperLinksWidget::class,
        ];
    }
}
