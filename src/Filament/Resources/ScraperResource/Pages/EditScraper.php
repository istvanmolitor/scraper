<?php

namespace Molitor\Scraper\Filament\Resources\ScraperResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Molitor\Scraper\Filament\Resources\ScraperResource;

class EditScraper extends EditRecord
{
    protected static string $resource = ScraperResource::class;

    public function getTitle(): string
    {
        return 'Edit Scraper';
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                SaveAction::make(),
                DeleteAction::make(),
            ]),
        ];
    }
}
