<?php

namespace Molitor\Scraper\Services;

use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class SimplePageParser extends PageParser
{
    public function getType(Crawler $crawler): string
    {
        return 'page';
    }

    public function getPriority(Crawler $crawler, string $type): int
    {
        return 1;
    }

    function getExpiration(Crawler $crawler, string $type, int $priority): Carbon
    {
        return Carbon::now()->addMonths(1);
    }

    public function getData(Crawler $crawler, string $type): array
    {
        return [
            'title' => $crawler->filter('title')->text(),
        ];
    }
}
