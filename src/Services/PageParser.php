<?php

declare(strict_types=1);

namespace Molitor\Scraper\Services;

use Carbon\Carbon;
use Molitor\HtmlParser\HtmlParser;
use Symfony\Component\DomCrawler\Crawler;

abstract class PageParser
{
    public function isValidUrl(Url $baseUrl, Url $url): bool
    {
        $host = $url->getHost();
        if ($host == '') {
            return true;
        }

        if ($host == $baseUrl->getHost()) {
            return true;
        }

        return false;
    }

    public function prepareUrl(Url $baseUrl, Url $url): ?Url
    {
        if ($this->isValidUrl($baseUrl, $url)) {
            return Url::prepare($baseUrl, $url);
        }
        return null;
    }

    abstract public function getType(HtmlParser $html): string;

    abstract public function getPriority(HtmlParser $html, string $type): int;

    abstract function getExpiration(HtmlParser $html, string $type, int $priority): Carbon;

    abstract public function getData(HtmlParser $html, string $type): array;



    public function getLinks(HtmlParser $html, Url $baseUrl): array
    {
        $links = $html->getLinks()->map(function (HtmlParser $link) {
            return $link->getAttribute('href');
        });

        dd($links);

        return array_values(array_unique(array_filter($links)));
    }
}
