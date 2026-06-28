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
        $this->addColumn('id')->setOrderable()->setHidden();
        $this->addColumn('name')->setLabel('Név')->setSearchable()->setOrderable();
        $this->addColumn('base_url')->setLabel('Alap URL')->setSearchable()->setOrderable();
        $this->addColumn('enabled')->setLabel('Aktív')->setOrderable();
        $this->addColumn('chunk_size')->setLabel('Chunk méret')->setOrderable();
        $this->addColumn('created_at')->setLabel('Létrehozva')->setOrderable();
    }

    protected function getDefaultSort(): string
    {
        return 'id';
    }

    protected function getDefaultDirection(): string
    {
        return 'desc';
    }

    public function query(Builder $query): Builder
    {
        return $query->withCount([
            'scraperUrls',
            'scraperUrls as downloaded_urls_count' => static fn ($q) => $q->whereNotNull('downloaded_at'),
        ]);
    }

    protected function getPerPage(): int
    {
        return min(100, max(1, $this->request->integer('per_page', 15)));
    }
}
