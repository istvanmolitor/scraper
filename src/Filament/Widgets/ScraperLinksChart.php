<?php

namespace Molitor\Scraper\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;

class ScraperLinksChart extends ChartWidget
{
    protected ?string $heading = 'Scraper linkek';

    public static function canView(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        /** @var ScraperUrlRepositoryInterface $scraperUrlRepository */
        $scraperUrlRepository = app(ScraperUrlRepositoryInterface::class);

        $total = $scraperUrlRepository->getNumAllUrls();
        $downloaded = $scraperUrlRepository->getNumDownloadedUrls();
        $fresh = $scraperUrlRepository->getNumFreshUrls();

        return [
            'labels' => [
                'Összes',       // total stored links
                'Letöltve',     // downloaded links
                'Kész',        // downloaded & not expired
            ],
            'datasets' => [[
                'label' => 'Scraper linkek',
                'data' => [
                    $total,
                    $downloaded,
                    $fresh,
                ],
                'backgroundColor' => [
                    '#60a5fa', // blue
                    '#34d399', // green
                    '#f59e0b', // amber
                ],
            ]],
        ];
    }
}
