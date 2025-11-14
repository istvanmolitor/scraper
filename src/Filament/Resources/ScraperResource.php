<?php

namespace Molitor\Scraper\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Filament\Resources\ScraperResource\Pages;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Rules\DomainRule;

class ScraperResource extends Resource
{
    protected static ?string $model = Scraper::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): string
    {
        return __('scraper::messages.navigation.group_tools');
    }

    public static function getNavigationLabel(): string
    {
        return __('scraper::messages.navigation.scrapers');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('enabled')->label(__('scraper::messages.scraper.fields.enabled'))->default(true),
            TextInput::make('name')
                ->label(__('scraper::messages.scraper.fields.name'))
                ->maxLength(255)
                ->required(),
            TextInput::make('base_url')
                ->label(__('scraper::messages.scraper.fields.base_url'))
                ->url()
                ->rules([new DomainRule()])
                ->maxLength(255)
                ->required()
                ->unique()
                ->disabledOn('edit'),
            Toggle::make('robots_txt')->label(__('scraper::messages.scraper.fields.robots_txt'))->default(true),
            Toggle::make('follow_links')->label(__('scraper::messages.scraper.fields.follow_links'))->default(false),

            TextInput::make('chunk_size')
                ->label(__('scraper::messages.scraper.fields.chunk_size'))
                ->numeric()
                ->required()
                ->default(1000)
                ->minValue(1)
                ->maxValue(100000),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('enabled')->boolean()->label(__('scraper::messages.scraper.table.enabled')),
                TextColumn::make('name')->label(__('scraper::messages.scraper.table.name'))->searchable()->sortable(),
                TextColumn::make('base_url')->label(__('scraper::messages.scraper.table.base_url'))->searchable()->wrap(),
                TextColumn::make('scraper_urls_count')
                    ->label(__('scraper::messages.scraper.table.urls_count'))
                    ->counts('scraperUrls')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Action::make('products')
                    ->label(__('scraper::messages.scraper.actions.links'))
                    ->icon('heroicon-o-link')
                    ->url(function ($record) {
                        return 'scraper-urls?scraper_id=' . $record->getKey();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScrapers::route('/'),
            'create' => Pages\CreateScraper::route('/create'),
            'edit' => Pages\EditScraper::route('/{record}/edit'),
        ];
    }
}
