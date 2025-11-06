<?php

namespace Molitor\Scraper\Filament\Resources;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;
use Molitor\Scraper\Models\ScraperUrl;
use Molitor\Scraper\Services\ScraperService;

class ScraperUrlResource extends Resource
{
    protected static ?string $model = ScraperUrl::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-link';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'scraper');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Scraper URL')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('scraper_id')
                            ->label('Scraper')
                            ->relationship('scraper', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => request()->integer('scraper_id') ?: null)
                            ->required(),
                        TextInput::make('type')
                            ->label('Type')
                            ->maxLength(50),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('hash')
                            ->label('Hash')
                            ->maxLength(64)
                            ->disabled(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('priority')
                            ->label('Priority')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100000),
                        TextInput::make('parent_id')
                            ->label('Parent ID')
                            ->numeric(),
                        DateTimePicker::make('downloaded_at')
                            ->label('Downloaded at')
                            ->native(false)
                            ->seconds(false),
                    ]),
                    Grid::make(2)->schema([
                        DateTimePicker::make('expiration_at')
                            ->label('Expiration at')
                            ->native(false)
                            ->seconds(false),
                        KeyValue::make('meta_data')
                            ->label('Meta data')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])->columns(2),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Type')->sortable()->searchable(),
                TextColumn::make('url')->label('URL')->wrap()->searchable(),
                TextColumn::make('priority')->label('Priority')->sortable(),
                TextColumn::make('downloaded_at')->dateTime()->label('Downloaded'),
                TextColumn::make('expiration_at')->dateTime()->label('Expires'),
            ])
            ->filters([
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('download')
                        ->label('Letöltés')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): void {

                            /** @var ScraperService $scraperService */
                            $scraperService = app(ScraperService::class);

                            foreach ($records as $record) {
                                $scraperService->downloadScraperUrl($record);
                            }

                            Notification::make()
                                ->title('Letöltés elindítva')
                                ->body('A kiválasztott URL-ek letöltése folyamatban van.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScraperUrls::route('/'),
            'create' => Pages\CreateScraperUrl::route('/create'),
            'edit' => Pages\EditScraperUrl::route('/{record}/edit'),
        ];
    }
}
