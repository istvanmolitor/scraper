<?php

namespace Molitor\Scraper\Services;

use Carbon\Carbon;

class SimplePageParser extends PageParser
{

    public function makeType(): ?string
    {
        return 'page';
    }

    public function makeData(): ?array
    {
        return null;
    }

    public function makeExpiration(): ?Carbon
    {
        return Carbon::now()->addMonths(1);
    }

    public function makePriority(): int
    {
        return 1;
    }
}
