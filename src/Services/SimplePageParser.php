<?php

namespace Molitor\Scraper\Services;

use Carbon\Carbon;
use Molitor\HtmlParser\HtmlParser;

class SimplePageParser extends PageParser
{
    public function getType(HtmlParser $html): string
    {
        return 'page';
    }

    public function getPriority(HtmlParser $html, string $type): int
    {
        return 1;
    }

    function getExpiration(HtmlParser $html, string $type, int $priority): Carbon
    {
        return Carbon::now()->addMonths(1);
    }

    public function getData(HtmlParser $html, string $type): array
    {
        return [
            'title' => $html->getTitle(),
        ];
    }
}
