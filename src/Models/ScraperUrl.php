<?php

namespace Molitor\Scraper\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function scraper()
    {
        return $this->belongsTo(Scraper::class, 'scraper_id');
    }
}
