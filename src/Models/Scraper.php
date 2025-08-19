<?php

declare(strict_types=1);

namespace Molitor\Scraper\Models;

use Illuminate\Database\Eloquent\Model;

class Scraper extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'enabled',
        'robots_txt',
        'follow_links',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'robots_txt' => 'bool',
        'follow_links' => 'bool',
    ];

    public function __toString()
    {
        return $this->base_url;
    }

    public function scraperUrls()
    {
        return $this->hasMany(ScraperUrl::class, 'scraper_id');
    }
}
