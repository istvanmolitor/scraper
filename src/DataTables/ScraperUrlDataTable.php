<?php

declare(strict_types=1);

namespace Molitor\Scraper\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Admin\DataTables\DataTable;
use Molitor\Scraper\Http\Resources\ScraperUrlResource;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperUrlDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return ScraperUrl::class;
    }

    protected function getResourceClass(): string
    {
        return ScraperUrlResource::class;
    }

    protected function initColumns(): void
    {
        $this->addColumn('id')->setOrderable()->setHidden();
        $this->addColumn('url')->setLabel('URL')->setSearchable()->setOrderable();
        $this->addColumn('type')->setLabel('Típus')->setSearchable()->setOrderable();
        $this->addColumn('priority')->setLabel('Prioritás')->setOrderable();
        $this->addColumn('downloaded_at')->setLabel('Letöltve')->setOrderable();
        $this->addColumn('expiration_at')->setLabel('Lejárat')->setOrderable();
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

    protected function getBaseQuery(): Builder
    {
        return ScraperUrl::query()->with('scraper');
    }

    protected function applyFilters(Builder $query): Builder
    {
        $query = parent::applyFilters($query);

        if ($this->request->filled('scraper_id')) {
            $query->where('scraper_id', $this->request->integer('scraper_id'));
        }

        return $query;
    }

    protected function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            'scraper_id' => $this->request->input('scraper_id'),
        ]);
    }
}
