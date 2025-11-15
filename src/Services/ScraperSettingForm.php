<?php

namespace Molitor\Scraper\Services;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Gate;
use Molitor\Setting\Services\SettingForm;

class ScraperSettingForm extends SettingForm
{
    public function getSlug(): string
    {
        return 'scraper';
    }

    public function getLabel(): string
    {
        return 'Scraper';
    }

    public function canAccess(): bool
    {
        return parent::canAccess() && Gate::allows('acl', 'scraper');
    }

    public function getForm(): array
    {
        return [
            Toggle::make('worker_enabled')->label('Engedélyezve'),
            TextInput::make('limit')
                ->label('Korlátozás')
                ->numeric()
                ->minValue(1)
                ->maxValue(10000)
                ->step(1),
        ];
    }

    public function getFormFields(): array
    {
        return [
            'worker_enabled',
            'limit',
        ];
    }

    public function getDefaults(): array
    {
        return [
            'worker_enabled' => true,
            'limit' => 1000,
        ];
    }
}
