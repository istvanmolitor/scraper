<?php

namespace Molitor\Scraper\Filament\Resources\ScraperResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Molitor\Scraper\Filament\Resources\ScraperResource;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Services\ScraperService;

class EditScraper extends EditRecord
{
    protected static string $resource = ScraperResource::class;

    public function getTitle(): string
    {
        return __('scraper::messages.scraper.pages.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make(),
            ]),
        ];
    }

    protected function getRedirectUrl(): string
    {
        /** @var Scraper $scraper */
        $scraper = $this->record;

        return ScraperUrlResource::getUrl('index', [
            'scraper_id' => $scraper->id,
        ]);
    }

    public function afterSave()
    {
        /** @var Scraper $scraper */
        $scraper = $this->record;

        /** @var ScraperService $scraperService */
        $scraperService = app(ScraperService::class);
        $scraperService->updateBaseLinks($scraper);
    }
}
