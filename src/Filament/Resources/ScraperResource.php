<?php

namespace Molitor\Scraper\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Filament\Resources\ScraperResource\Pages;
use Molitor\Scraper\Models\Scraper;

class ScraperResource extends Resource
{
    protected static ?string $model = Scraper::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationGroup(): string
    {
        return 'Tools';
    }

    public static function getNavigationLabel(): string
    {
        return 'Scrapers';
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('enabled')->label('Enabled')->default(true),
            TextInput::make('name')
                ->label('Name')
                ->maxLength(255)
                ->required(),
            TextInput::make('base_url')
                ->label('Base URL')
                ->url()
                ->maxLength(255)
                ->required(),

            Toggle::make('robots_txt')->label('Respect robots.txt')->default(true),
            Toggle::make('follow_links')->label('Follow links')->default(false),

            TextInput::make('chunk_size')
                ->label('Chunk size')
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
                IconColumn::make('enabled')->boolean()->label('Enabled'),
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('base_url')->label('Base URL')->searchable()->wrap(),
            ])
            ->filters([
            ])
            ->actions([
                Action::make('products')
                    ->label('Linkek')
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
