<?php

namespace Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource;

class EditScraperUrl extends EditRecord
{
    protected static string $resource = ScraperUrlResource::class;

    public function getTitle(): string
    {
        return 'Edit URL';
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make(),
            ]),
        ];
    }
}
