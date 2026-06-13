<?php

namespace Molitor\Scraper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Molitor\Scraper\Rules\ScraperUrlRule;

class StoreScraperUrlRequest extends FormRequest
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
        $scraperId = (int) $this->input('scraper_id');

        return [
            'scraper_id' => ['required', 'integer', 'exists:scrapers,id'],
            'type' => ['nullable', 'string', 'max:255'],
            'url' => [
                'required',
                'string',
                'url',
                'max:512',
                Rule::unique('scraper_urls', 'url')->where(fn ($query) => $query->where('scraper_id', $scraperId)),
                new ScraperUrlRule($scraperId),
            ],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'expiration_at' => ['nullable', 'date'],
        ];
    }
}
