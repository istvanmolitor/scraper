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

    abstract public function getType(Crawler $crawler): string;

    abstract public function getPriority(Crawler $crawler, string $type): int;

    abstract function getExpiration(Crawler $crawler, string $type, int $priority): Carbon;

    abstract public function getData(Crawler $crawler, string $type): array;

    public function getLinks(Crawler $crawler): array
    {
        $baseUrl = new Url($crawler->getBaseHref());

        $links = $crawler->filter('a[href]')->each(function (Crawler $node) use ($baseUrl){
            try {
                $url = new Url($node->link()->getUri());
                return (string)$this->prepareUrl($baseUrl, $url);
            } catch (\Exception $e) {
                return null;
            }
        });

        return array_values(array_unique(array_filter($links)));
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'html' => $this->html,
            'base_url' => $this->baseUrl,
            'type' => $this->type,
            'priority' => $this->priority,
            'expiration' => $this->expiration,
            'data' => $this->data,
        ];
    }
}
