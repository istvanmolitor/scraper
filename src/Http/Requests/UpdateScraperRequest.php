<?php

namespace Molitor\Scraper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateScraperRequest extends FormRequest
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
        $scraperId = (int) ($this->route('scraper') ?? $this->route('id'));

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('scrapers', 'name')->ignore($scraperId)],
            'enabled' => ['boolean'],
            'robots_txt' => ['boolean'],
            'follow_links' => ['boolean'],
            'chunk_size' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
