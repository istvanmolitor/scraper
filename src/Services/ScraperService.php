<?php

namespace Molitor\Scraper\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Molitor\HtmlParser\HtmlParser;
use Molitor\Scraper\Exceptions\InvalidDomain;
use Molitor\Scraper\Exceptions\ScraperNameAlreadyExists;
use Molitor\Scraper\Exceptions\ScraperNotExists;
use Molitor\Scraper\Events\ScraperUrlUpdateEvent;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Models\ScraperUrl;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;

class ScraperService
{
    private ?Command $command = null;

    private Client $client;

    private array $tasks = [];

    private array $domainMap = [];

    private array $scraperIdMap = [];

    private array $registeredLinks = [];

    public function __construct(
        private ScraperRepositoryInterface    $scraperRepository,
        private ScraperUrlRepositoryInterface $scraperUrlRepository,
    )
    {
        $this->client = new Client();
        $this->init();
    }

    /*Init*************************************************************************************/

    protected function init(): void
    {
        $this->scraperIdMap = [];
        $this->domainMap = [];

        foreach ($this->scraperRepository->getAll() as $scraper) {
            $this->initScraper($scraper);
        }

        $parsers = config('scraper.parsers', []);
        foreach ($parsers as $domain => $className) {
            if($this->domainExists($domain)) {
                $scraper = $this->getScraperByLink($domain);
                $this->addPageParser($scraper, $className);
            }
        }

        $this->clearRegisteredLinks();
    }

    protected function initScraper(Scraper $scraper): void
    {
        $domain = $this->getDomainByScraper($scraper);
        $parser = new SimplePageParser(new Url($domain));

        $this->scraperIdMap[$scraper->id] = [
            'domain' => $domain,
            'scraper' => $scraper,
            'pageParser' => $parser,
        ];
        $this->domainMap[$domain] = [
            'id' => $scraper->id,
            'scraper' => $scraper,
            'pageParser' => $parser,
        ];
    }

    protected function addPageParser(Scraper $scraper, string $className): bool
    {
        $domain = $this->getDomainByScraper($scraper);
        $pageParser = new $className(new Url($domain));
        if (!($pageParser instanceof PageParser)) {
            return false;
        }

        $this->domainMap[$domain]['pageParser'] = $pageParser;
        $this->scraperIdMap[$scraper->id]['pageParser'] = $pageParser;

        return true;
    }

    /*exists*************************************************************************************/

    public function scraperIdExists(int $scraperId): bool
    {
        return isset($this->scraperIdMap[$scraperId]);
    }

    public function domainExists(string $domain): bool
    {
        return isset($this->domainMap[$domain]);
    }

    /*validate*************************************************************************************/

    public function validateScraperId(int $scraperId): void
    {
        if (!$this->scraperIdExists($scraperId)) {
            throw new ScraperNotExists("Scraper with id {$scraperId} does not exists.");
        }
    }

    public function validateDomain(string $domain): void
    {
        if (!$this->domainExists($domain)) {
            throw new InvalidDomain("Invalid base url: {$domain}");
        }
    }

    /*getDomain*************************************************************************************/

    public function getDomainByScraperId(int $scraperId): string
    {
        $this->validateScraperId($scraperId);
        return $this->scraperIdMap[$scraperId]['domain'];
    }

    public function getDomainByUrl(Url $url): string
    {
        $domain = $url->getSchemeAndHost();
        $this->validateDomain($domain);
        return $domain;
    }

    public function getDomainByLink(string $link): string
    {
        return $this->getDomainByUrl(new Url($link));
    }

    public function getDomainByScraper(Scraper $scraper): string
    {
        return $scraper->base_url;
    }

    public function getDomainByScraperUrl(ScraperUrl $scraperUrl): string
    {
        return $this->getDomainByScraperId($scraperUrl->scraper_id);
    }

    /*Scraper*************************************************************************************/

    public function getScraperById(int $scraperId): Scraper
    {
        $this->validateScraperId($scraperId);
        return $this->scraperIdMap[$scraperId]['scraper'];
    }

    public function getScraperByUrl(Url $url): Scraper
    {
        $domain = $this->getDomainByUrl($url);
        return $this->domainMap[$domain]['scraper'];
    }

    public function getScraperByLink(string $link): Scraper
    {
        return $this->getScraperByUrl(new Url($link));
    }

    public function getScraperByName(string $name): ?Scraper
    {
        return $this->scraperRepository->getByName($name);
    }

    public function getScraperByScraperUrl(ScraperUrl $scraperUrl): Scraper
    {
        return $this->getScraperById($scraperUrl->scraper_id);
    }

    /*getScraperUrl*************************************************************************************/

    public function getScraperUrlById(int $scraperUrlId): ?ScraperUrl
    {
        return $this->scraperUrlRepository->getById($scraperUrlId);
    }

    public function getScraperUrlByLink(string $link): ?ScraperUrl
    {
        $scraper = $this->getScraperByLink($link);
        return $this->scraperUrlRepository->getScraperUrl($scraper, $link);
    }

    /*getPageParser*************************************************************************************/

    public function getPageParserByScraperId(int $scraperId): ?PageParser
    {
        return $this->scraperIdMap[$scraperId]['pageParser'];
    }
    public function getPageParserByScraper(Scraper $scraper): ?PageParser
    {
        return $this->getPageParserByScraperId($scraper->id);
    }

    public function getPageParserByDomain(string $domain): ?PageParser
    {
        return $this->domainMap[$domain]['pageParser'];
    }

    public function getPageParserByUrl(Url $url): ?PageParser
    {
        $domain = $this->getDomainByUrl($url);
        return $this->getPageParserByDomain($domain);
    }

    /**************************************************************************************/

    public function createScraper(string $name, string $baseUrl, bool $robotsTxt, bool $followLinks, bool $enabled): Scraper
    {
        $scraper = $this->getScraperByName($name);
        if($scraper) {
            throw new ScraperNameAlreadyExists('Scraper name already exists: ' . $name);
        }

        $url = new Url($baseUrl);
        if($baseUrl !== $url->getSchemeAndHost()) {
            throw new InvalidDomain('Invalid base url');
        }

        $scraper = $this->scraperRepository->create($name, $baseUrl, $robotsTxt, $followLinks, $enabled);

        $this->init();
        $this->addBaseLinks($scraper);
        return $scraper;
    }

    public function addBaseLinks(Scraper $scraper): void
    {
        if($scraper->robots_txt) {
            $this->storeRobotsTxt($scraper);
        }
        if($scraper->follow_links) {
            $this->storeLinks([$scraper->base_url], $scraper, 'domain', 0);
        }
    }

    /*Download*************************************************************************************/

    /**
     * @param ScraperUrl $scraperUrl
     * @return void
     */
    public function downloadScraperUrl(ScraperUrl $scraperUrl): void
    {
        switch ($scraperUrl->type) {
            case 'robotstxt':
                $this->downloadRobotsTxt($scraperUrl);
                break;
            case 'sitemap':
                $this->downloadSitemap($scraperUrl);
                break;
            default:
                $this->downloadPage($scraperUrl);
        }
        $scraperUrl->touch('downloaded_at');
    }

    private function downloadPage(ScraperUrl $scraperUrl): void
    {
        $result = $this->client->get($scraperUrl->url);
        $status = $result->getStatusCode();

        Log::channel('scraper')->info('Scraper started', [
            'id' => $scraperUrl->id,
            'url' => $scraperUrl->url,
            'status' => $status,
        ]);

        if($status !== 200) {
            return;
        }

        $pageContent = $result->getBody()->getContents();
        if(!$pageContent) {
            return;
        }

        $html = new HtmlParser($pageContent);

        $scraper = $this->getScraperByScraperUrl($scraperUrl);

        $pageParser = $this->getPageParserByScraper($scraper);
        $pageParser->reset();
        $pageParser->setLink($scraperUrl->url);
        $pageParser->setHtml($html);

        $pageParser->parseType('page');
        $pageParser->parsePriority(1);
        $pageParser->parseExpiration((new Carbon())->addMonths(1));
        $pageParser->parseData(null);

        $scraperUrl->fill([
            'type' => $pageParser->getType(),
            'priority' => $pageParser->getPriority(),
            'expiration_at' => $pageParser->getExpiration(),
        ]);
        $scraperUrl->save();

        event(new ScraperUrlUpdateEvent($scraperUrl, $pageParser->getData()));

        if ($scraper->follow_links) {
            $this->storeLinks($html->findLinks(), $scraperUrl, 'page', 1);
        }
    }

    /*Robots.txt*************************************************************************************/

    public function getRobotsTxtLinkByScraper(Scraper $scraper): string
    {
        return $this->getDomainByScraper($scraper) . '/robots.txt';
    }

    public function getRobotsTxtByScraper(Scraper $scraper): ?ScraperUrl
    {
        return $this->scraperUrlRepository->getScraperUrl($scraper, $this->getRobotsTxtLinkByScraper($scraper));
    }

    private function storeRobotsTxt(Scraper $scraper): ScraperUrl
    {
        $scraperUrl = $this->getRobotsTxtByScraper($scraper);
        if($scraperUrl) {
            return $scraperUrl;
        }
        $this->storeLinks([
            $this->getRobotsTxtLinkByScraper($scraper)
        ], $scraper, 'robotstxt', 0);
        return $this->getRobotsTxtByScraper($scraper);
    }

    private function downloadRobotsTxt(ScraperUrl $scraperUrl): void
    {
        $robotsTxtContent = $this->downloadContent($scraperUrl->url);

        $sitemaps = [];
        $lines = explode("\n", $robotsTxtContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(?i)sitemap:\s*(.+)$/', $line, $matches)) {
                $sitemapUrl = trim($matches[1]);
                if (filter_var($sitemapUrl, FILTER_VALIDATE_URL)) {
                    $sitemaps[] = $sitemapUrl;
                }
            }
        }

        $sitemaps = array_unique($sitemaps);

        $this->storeLinks($sitemaps, $scraperUrl, 'sitemap', 0);

        $scraperUrl->expiration_at = Carbon::now()->addDays();
        $scraperUrl->save();
    }

    /*Sitemap*************************************************************************************/

    private function downloadSitemap(ScraperUrl $scraperUrl): void
    {
        $sitemapContent = $this->downloadContent($scraperUrl->url);
        $xml = simplexml_load_string($sitemapContent, "SimpleXMLElement", LIBXML_NOCDATA);
        $sitemapData = json_decode(json_encode($xml), true);

        if(isset($sitemapData['sitemap'])) {
            $links = [];
            foreach($sitemapData['sitemap'] as $sitemap) {
                $links[] = $sitemap['loc'];
            }
            $this->storeLinks($links, $scraperUrl, 'sitemap', 0);
        }
        elseif(isset($sitemapData['url'])) {
            $links = [];
            foreach($sitemapData['url'] as $sitemap) {
                $links[] = $sitemap['loc'];
            }
            $this->storeLinks($links, $scraperUrl, 'page', 1);
        }

        $scraperUrl->expiration_at = Carbon::now()->addDays();
        $scraperUrl->save();
    }

    /**************************************************************************************/

    /**
     * Visszaadja a feladatokat amiken végig kell menni.
     * @return array
     */
    public function getTasks($limit): Collection
    {
        $scrapers = $this->scraperRepository->getEnabledScrapers();

        $numScrapers = $scrapers->count();

        $scraperLimit = ceil($limit / $numScrapers);

        $tasks = collect();
        foreach ($scrapers as $scraper) {
            $tasks = $this->scraperUrlRepository->getTasksByScraper($scraper, $scraperLimit);
            $tasks = $tasks->merge($tasks);
        }

        return $tasks;
    }

    /*Registered links**********************************************************************************************/

    public function storeLink(string $link): ScraperUrl
    {
        $url = new Url($link);
        $domain = $this->getDomainByUrl($url);
        $scraper = $this->getScraperByLink($domain);
        $parser = $this->getPageParserByDomain($scraper);
        $parser->reset();
        $parser->prepareLink($link);
        $this->storeLinks([$link], $scraper, $parser->getType(), $parser->getPriority());
        return $this->getScraperUrlByLink($link);
    }

    protected function storeLinks(array $links, Scraper|ScraperUrl $parent, string $type = null, int $priority = null): void
    {
        if($parent instanceof Scraper) {
            $scraperId = $parent->id;
            $parentId = null;
        }
        else {
            $scraperId = $parent->scraper_id;
            $parentId = $parent->id;
        }

        $pageParser = $this->getPageParserByScraperId($scraperId);
        foreach ($links as $link) {
            $pageParser->reset();
            $pageParser->setLink($link);
            $preparedLink = $pageParser->getLink();

            if($preparedLink) {
                $this->addRegisteredLink(
                    $scraperId,
                    $preparedLink,
                    $type,
                    $priority,
                    $parentId
                );
            }
        }
        $this->storeRegisteredLinks();
    }

    protected function clearRegisteredLinks(): void
    {
        $this->registeredLinks = [];
    }

    protected function addRegisteredLink(int $scraperId, string $link, ?string $type, ?int $priority, ?int $parentId): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $this->registeredLinks[$link] = [
            'scraper_id' => $scraperId,
            'type' => $type,
            'hash' => md5($link),
            'url' => $link,
            'priority' => $priority,
            'parent_id' => $parentId,
            'downloaded_at' => null,
            'expiration_at' => $now,
            'created_at' => $now,
        ];
        if (count($this->registeredLinks) >= 500) {
            $this->storeRegisteredLinks();
        }
    }

    protected function storeRegisteredLinks(): void
    {
        $this->scraperUrlRepository->storeUrls(array_values($this->registeredLinks));
        $this->clearRegisteredLinks();
    }

    /***********************************************************************************************/

    private function downloadContent(string|array $urls): null|string|array
    {
        if (is_string($urls)) {
            $result = $this->client->get($urls);
            if($result->getStatusCode() === 200) {
                return $result->getBody()->getContents();
            }
            return null;
        }

        $promises = [];

        foreach ($urls as $url) {
            $promises[$url] = $this->client->getAsync($url);
        }

        $results = Promise\Utils::settle($promises)->wait();

        $htmlParsers = [];
        foreach ($results as $url => $result) {
            if ($result['state'] === 'fulfilled') {
                $htmlParsers[$url] = $result['value']->getBody()->getContents();
            } else {
                $htmlParsers[$url] = null;
            }
        }
        return $htmlParsers;
    }

    public function delay(Scraper $scraper): void
    {
        $parser = $this->getPageParserByScraper($scraper);
        if($parser) {
            $parser->delay();
        }
    }
}
