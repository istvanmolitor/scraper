<?php

declare(strict_types=1);

namespace Molitor\Scraper\Services;

class Url
{
    private string $scheme = '';
    private string $host = '';
    private string $path = '/';
    private string $query = '';
    private array $queryData = [];

    public function __construct(string $url = NULL)
    {
        if ($url) {
            $data = parse_url($url);

            if (isset($data['scheme'])) {
                $this->scheme = $data['scheme'];
            }

            if (isset($data['host'])) {
                $this->host = $data['host'];
            }

            if (isset($data['path'])) {
                if (substr($data['path'], 0, 1) == '/') {
                    $this->path = $data['path'];
                } else {
                    $this->path = '/' . $data['path'];
                }
            } else {
                $this->path = '/';
            }

            if (isset($data['query'])) {
                $data = $this->explodeQuery($data['query']);
                $this->query = $this->implodeQuery($data);
                $this->queryData = $data;
            }
        }
    }

    public function getExtension(): string
    {
        if ($this->path != '/') {
            $exp = explode('.', $this->path);
            $index = count($exp) - 1;
            if (isset($exp[$index])) {
                return strtolower($exp[$index]);
            }
        }
        return '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path === '/' ? '' : $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function getSchemeAndHost(): string
    {
        return $this->scheme . '://' . $this->host;
    }

    public function getPathAndQuery(): string
    {
        return $this->getPath() . ($this->query ? '?' . $this->query : '');
    }

    private function explodeQuery(string $query): array
    {
        if ($query) {
            $data = [];
            if ($query != '') {
                parse_str($query, $output);
                foreach ($output as $name => $value) {
                    if (!is_array($value)) {
                        $data[$name] = $value;
                    }
                }
            }
            ksort($data);
            return $data;
        }
        return [];
    }

    private function implodeQuery(array $data): string
    {
        if (count($data)) {
            $elements = [];
            foreach ($data as $name => $value) {
                $elements[] = $name . '=' . $value;
            }
            return implode('&', $elements);
        }
        return '';
    }

    public function __toString()
    {
        return $this->getSchemeAndHost() . $this->getPathAndQuery();
    }

    public function removeQueryParam(string $name): self
    {
        if (isset($this->queryData[$name])) {
            unset($this->queryData[$name]);
            $this->updateQuery();
        }
        return $this;
    }

    public function removeQueryParams(array $names): self
    {
        $update = false;
        foreach ($names as $name) {
            if (isset($this->queryData[$name])) {
                unset($this->queryData[$name]);
                $update = true;
            }
        }
        if ($update) {
            $this->updateQuery();
        }
        return $this;
    }

    protected function updateQuery(): void
    {
        $this->query = $this->implodeQuery($this->queryData);
    }

    public function setQueryPram(string $name, string $value): self
    {
        $this->queryData[$name] = $value;
        ksort($this->queryData);
        $this->updateQuery();
        return $this;
    }

    public function hasQueryParam(string $name): bool
    {
        return array_key_exists($name, $this->queryData);
    }

    public function getQueryParam(string $name): ?string
    {
        return $this->hasQueryParam($name) ? $this->queryData[$name] : null;
    }

    public static function prepare(Url $baseUrl, Url $url): ?Url
    {
        //Nem megfelelő az alap url ha nincs megadva host vagy séma
        if ($baseUrl->getHost() === '' || $baseUrl->getScheme() === '') {
            return null;
        }

        //Nem lehet javítani az url-t ha van séma és az nem egyezik meg az eredetivel.

        //Nem lehet javítani az url-t ha van host és nem egyezik meg az eredetivel.
        if ($url->getHost() !== '' && $baseUrl->getHost() !== $url->getHost()) {
            return null;
        }

        if ($url->getScheme() !== '') {
            $url->setScheme($baseUrl->getScheme());
        }

        $url->setHost($baseUrl->getHost());

        return $url;
    }
}
