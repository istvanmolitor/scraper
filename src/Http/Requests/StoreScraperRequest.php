<?php

namespace Molitor\Scraper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Rules\DomainRule;

class StoreScraperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:scrapers,name'],
            'base_url' => ['required', 'string', 'url', 'max:255', 'unique:scrapers,base_url', new DomainRule()],
            'enabled' => ['boolean'],
            'robots_txt' => ['boolean'],
            'follow_links' => ['boolean'],
            'chunk_size' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
