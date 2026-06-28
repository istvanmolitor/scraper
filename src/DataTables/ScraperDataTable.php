<?php

declare(strict_types=1);

namespace Molitor\Scraper\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Admin\DataTables\DataTable;
use Molitor\Scraper\Http\Resources\ScraperResource;
use Molitor\Scraper\Models\Scraper;

class ScraperDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return Scraper::class;
    }

    protected function getResourceClass(): string
    {
        return ScraperResource::class;
    }

    protected function initColumns(): void
    {
        $this->addColumn('id')->setOrderable();
        $this->addColumn('name')->setSearchable()->setOrderable();
        $this->addColumn('base_url')->setSearchable()->setOrderable();
        $this->addColumn('enabled')->setOrderable();
        $this->addColumn('chunk_size')->setOrderable();
        $this->addColumn('created_at')->setOrderable();
    }

    protected function getDefaultSort(): string
    {
        return 'id';
    }

    protected function getDefaultDirection(): string
    {
        return 'desc';
    }

    protected function getBaseQuery(): Builder
    {
        return Scraper::query()->withCount([
            'scraperUrls',
            'scraperUrls as downloaded_urls_count' => static fn ($q) => $q->whereNotNull('downloaded_at'),
        ]);
    }

    protected function getPerPage(): int
    {
        return min(100, max(1, $this->request->integer('per_page', 15)));
    }
}
