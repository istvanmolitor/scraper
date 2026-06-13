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
        $metaData = $html->parseMetaData();
        $image = $metaData['og:image']
            ?? $metaData['twitter:image']
            ?? $html->getImages()->getFirst()?->getAttribute('src');
        $author = $metaData['author']
            ?? $metaData['article:author']
            ?? $metaData['twitter:creator']
            ?? null;
        $description = $metaData['description']
            ?? $metaData['og:description']
            ?? $metaData['twitter:description']
            ?? null;
        $keywords = $metaData['keywords']
            ?? null;

        return [
            'title' => $html->getTitle(),
            'image' => $image,
            'author' => $author,
            'description' => $description,
            'keywords' => $keywords,
        ];
    }
}
