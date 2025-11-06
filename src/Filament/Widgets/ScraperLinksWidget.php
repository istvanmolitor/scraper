<?php

namespace Molitor\Scraper\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperLinksWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = ScraperUrl::count();
        $remaining = ScraperUrl::whereNull('downloaded_at')->count();

        return [
            Stat::make(__('scraper::messages.widgets.total_links'), $total)
                ->description(__('scraper::messages.widgets.total_links_description'))
                ->descriptionIcon('heroicon-m-link')
                ->color('primary'),

            Stat::make(__('scraper::messages.widgets.remaining_links'), $remaining)
                ->description(__('scraper::messages.widgets.remaining_links_description'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('warning'),
        ];
    }
}
