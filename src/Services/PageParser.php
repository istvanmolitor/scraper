<?php

declare(strict_types=1);

namespace Molitor\Scraper\Services;

use Carbon\Carbon;
use Molitor\HtmlParser\HtmlParser;

abstract class PageParser
{
    protected ?Url $baseUrl = null;

    protected ?Url $url = null;
    protected ?HtmlParser $html = null;

    protected ?string $type = null;
    protected ?int $priority = null;
    protected ?Carbon $expiration = null;
    protected ?array $data = null;

    public function __construct(Url $baseUrl)
    {
        $this->setBaseUrl($baseUrl);
    }

    abstract public function makeType(): ?string;

    abstract public function makeData(): ?array;

    abstract public function makeExpiration(): ?Carbon;

    abstract public function makePriority(): int;

    /*********************************************************/

    public function reset(): void
    {
        $this->url = null;
        $this->html = null;
        $this->type = null;
        $this->priority = null;
        $this->expiration = null;
        $this->data = null;
    }

    public function setUrl(?Url $url): void
    {
        if ($url === null) {
            $this->url = null;
        }
        else {
            $this->url = $this->prepareUrl($url);
        }
    }

    public function getUrl(): ?Url {
        return $this->url;
    }

    public function setLink(?string $link): void
    {
        if ($link) {
            $this->setUrl(new Url($link));
        } else {
            $this->url = null;
        }
    }

    public function getLink(): ?string {
        return $this->url ? (string)$this->url : null;
    }

    public function setHtml(?HtmlParser $html): void
    {
        $this->html = $html;
    }

    public function getHtml(): ?HtmlParser {
        return $this->html;
    }

    public function setBaseUrl(Url $url): void
    {
        $this->baseUrl = $url;
    }

    public function getBaseUrl(): ?Url {
        return $this->baseUrl;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setExpiration(?Carbon $expiration): void
    {
        $this->expiration = $expiration;
    }

    public function getExpiration(): ?Carbon
    {
        return $this->expiration;
    }

    /*********************************************************/

    public function isValidUrl(Url $url): bool
    {
        $host = $url->getHost();
        if ($host == '') {
            return true;
        }

        if ($host == $this->baseUrl->getHost()) {
            return true;
        }

        return false;
    }

    public function prepareUrl(Url $url): ?Url
    {
        if ($this->isValidUrl($url)) {
            return Url::prepare($this->baseUrl, $url);
        }
        return null;
    }

    public function prepareLink(string $link): ?string
    {
        $prepareUrl = $this->prepareUrl(new Url($link));
        return $prepareUrl ? (string)$prepareUrl : null;
    }

    /*********************************************************/

    public function parseType(?string $defaultType): void
    {
        $type = $this->makeType();
        $this->type = $type ?: $defaultType;
    }

    public function parsePriority(?int $defaultPriority): void
    {
        $priority = $this->makePriority();
        $this->priority = $priority ?: $defaultPriority;
    }

    public function parseExpiration(?Carbon $defaultExpiration): void
    {
        $expiration = $this->makeExpiration();
        $this->expiration = $expiration ?: $defaultExpiration;
    }

    public function parseData(?array $defaultData): void
    {
        $data = $this->makeData();
        $this->data = $data ?: $defaultData;
    }

    public function getLinks(): array
    {
        if(!$this->html) {
            return [];
        }

        $links = $this->html->findLinks();
        $validLinks = [];
        foreach($links as $link) {
            if($this->isValidUrl(new Url($link))) {
                $validLinks[] = $link;
            }
        }
        return $validLinks;
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
