<?php

namespace Molitor\Scraper\Rules;

use Illuminate\Contracts\Validation\Rule;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Models\ScraperUrl;
use Molitor\Scraper\Services\Url;

class ScraperUrlRule implements Rule
{
    private ?string $domain = null;

    public function __construct(private int $scraperId)
    {
        $scraper = Scraper::find($this->scraperId);
        if (!$scraper) {
            return;
        }

        $domain = new Url($scraper->base_url);
        $this->domain = $domain->getSchemeAndHost();
    }

    public function passes($attribute, $value)
    {
        if (!$this->domain) {
            return false;
        }

        $url = new Url($value);
        return $this->domain === $url->getSchemeAndHost();
    }

    public function message()
    {
        if (!$this->domain) {
            return 'Érvénytelen scraper azonosító.';
        }

        return 'Hibás domain (' . $this->domain . ').';
    }
}
