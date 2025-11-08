<?php

namespace Molitor\Scraper\Rules;

use Illuminate\Contracts\Validation\Rule;
use Molitor\Scraper\Services\Url;

class DomainRule implements Rule
{

    public function passes($attribute, $value)
    {
        $url = new Url($value);
        return $url->getSchemeAndHost() === $value;
    }

    public function message()
    {
        return 'Hibás formátum (pl. https://example.com).';
    }
}
