<?php

namespace Molitor\Scraper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScraperUrl extends Model
{
    protected $fillable = [
        'scraper_id',
        'type',
        'hash',
        'url',
        'priority',
        'parent_id',
        'downloaded_at',
        'meta_data',
        'expiration_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'expiration_at' => 'datetime',
        'meta_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (ScraperUrl $scraperUrl) {
            $scraperUrl->hash = md5($scraperUrl->url);
        });
    }

    public function scraper(): BelongsTo
    {
        return $this->belongsTo(Scraper::class, 'scraper_id');
    }
}
