<?php

namespace Molitor\Scraper\Filament\Resources;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Scraper')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->maxLength(255)
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        Toggle::make('enabled')->label('Enabled')->default(true),
                        Toggle::make('robots_txt')->label('Respect robots.txt')->default(true),
                        Toggle::make('follow_links')->label('Follow links')->default(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('chunk_size')
                            ->label('Chunk size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100000),
                        DateTimePicker::make('blocked')
                            ->label('Blocked until')
                            ->native(false)
                            ->seconds(false),
                    ]),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('base_url')->label('Base URL')->searchable()->wrap(),
                IconColumn::make('enabled')->boolean()->label('Enabled'),
                IconColumn::make('robots_txt')->boolean()->label('Robots'),
                IconColumn::make('follow_links')->boolean()->label('Follow'),
                TextColumn::make('chunk_size')->label('Chunk'),
                TextColumn::make('blocked')->dateTime()->label('Blocked'),
            ])
            ->filters([
            ])
            ->actions([
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
