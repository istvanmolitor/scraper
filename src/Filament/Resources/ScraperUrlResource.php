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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Molitor\Scraper\Filament\Resources\ScraperUrlResource\Pages;
use Molitor\Scraper\Models\ScraperUrl;
use Molitor\Scraper\Rules\ScraperUrlRule;
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
            Grid::make(2)->schema([
                Select::make('scraper_id')
                    ->label(__('scraper::messages.scraper_url.fields.scraper'))
                    ->relationship('scraper', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => request()->integer('scraper_id') ?: null)
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $operation) => $operation !== 'edit')
                    ->required(),
            ]),
            TextInput::make('url')
                ->label(__('scraper::messages.scraper.fields.url'))
                ->url()
                ->unique()
                ->maxLength(255)
                ->required()
                ->disabledOn('edit')
                ->rules([
                    fn ($get) => new ScraperUrlRule($get('scraper_id')),
                ]),
            Grid::make(3)->schema([
                TextInput::make('priority')
                    ->label(__('scraper::messages.scraper_url.fields.priority'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100000),
                DateTimePicker::make('expiration_at')
                    ->label(__('scraper::messages.scraper_url.fields.expiration_at'))
                    ->native(false)
                    ->seconds(false),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('ok')
                    ->label(__('scraper::messages.scraper_url.fields.ready'))
                    ->boolean()
                    ->state(fn (ScraperUrl $record) => $record->downloaded_at !== null && optional($record->expiration_at)->isFuture()),
                TextColumn::make('type')->label(__('scraper::messages.scraper_url.fields.type'))->sortable()->searchable(),
                TextColumn::make('url')->label(__('scraper::messages.scraper_url.fields.url'))->wrap()->searchable(),
                TextColumn::make('priority')->label(__('scraper::messages.scraper_url.fields.priority'))->sortable(),
                TextColumn::make('expiration_at')->dateTime()->label(__('scraper::messages.scraper_url.fields.expires')),
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
                        ->label(__('scraper::messages.scraper_url.bulk.download'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): void {

                            /** @var ScraperService $scraperService */
                            $scraperService = app(ScraperService::class);

                            foreach ($records as $record) {
                                $scraperService->downloadScraperUrl($record);
                            }

                            Notification::make()
                                ->title(__('scraper::messages.scraper_url.notifications.download_started.title'))
                                ->body(__('scraper::messages.scraper_url.notifications.download_started.body'))
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
