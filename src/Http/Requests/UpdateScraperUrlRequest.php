<?php

namespace Molitor\Scraper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateScraperUrlRequest extends FormRequest
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
            'type' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'expiration_at' => ['nullable', 'date'],
        ];
    }
}
