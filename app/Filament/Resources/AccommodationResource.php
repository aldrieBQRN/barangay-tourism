<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccommodationResource\Pages;
use App\Models\Accommodation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Room Details')
                    ->schema([
                        Forms\Components\Select::make('resort_id')
                            ->relationship('resort', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('name')
                            ->label('Room/Cottage Name')
                            ->placeholder('e.g., Deluxe Villa, Nipa Hut #1')
                            ->required(),
                        
                        Forms\Components\TextInput::make('capacity')
                            ->label('Max Capacity (Pax)')
                            ->numeric()
                            ->default(2)
                            ->required()
                            ->prefixIcon('heroicon-m-users'),

                        // PRICE GROUP
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('price_day_tour')
                                    ->label('Day Tour Rate')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->helperText('Price for 8am-5pm use'),

                                Forms\Components\TextInput::make('price_per_night')
                                    ->label('Overnight Rate')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->required(),
                            ])->columns(2)->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Amenities / Description')
                            ->placeholder('e.g. Air-conditioned, Beach View, Private CR')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('accommodations')
                            ->imageEditor()
                            ->columnSpanFull()
                            ->helperText('Upload a photo of the room or cottage.'),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Available for Booking')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Room Information')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('image')
                                ->grow(false)
                                ->circular()
                                ->disk('public'),
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                Infolists\Components\TextEntry::make('resort.name')
                                    ->icon('heroicon-m-building-office-2'),
                                Infolists\Components\TextEntry::make('description')
                                    ->markdown()
                                    ->prose()
                                    ->placeholder('No description provided.'),
                            ]),
                        ])->from('md'),
                    ]),

                Infolists\Components\Section::make('Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('capacity')
                            ->label('Max Capacity')
                            ->icon('heroicon-m-users')
                            ->suffix(' Pax'),
                        
                        Infolists\Components\TextEntry::make('price_day_tour')
                            ->money('PHP')
                            ->label('Day Rate')
                            ->placeholder('N/A'),

                        Infolists\Components\TextEntry::make('price_per_night')
                            ->money('PHP')
                            ->label('Night Rate')
                            ->weight('bold')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('is_available')
                            ->label('Current Status')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'AVAILABLE' : 'UNAVAILABLE'),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->disk('public')
                    ->label('Photo'),

                Tables\Columns\TextColumn::make('resort.name')
                    ->label('Resort')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Room Name')
                    ->searchable()
                    ->description(fn (Accommodation $record): string => $record->description ? \Illuminate\Support\Str::limit($record->description, 30) : ''),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->icon('heroicon-m-users')
                    ->formatStateUsing(fn (string $state): string => $state . ' Pax')
                    ->sortable(),

                // NEW: Show both prices
                Tables\Columns\TextColumn::make('price_day_tour')
                    ->money('PHP')
                    ->label('Day Rate')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('price_per_night')
                    ->money('PHP')
                    ->label('Night Rate')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Status')
                    ->trueLabel('Available')
                    ->falseLabel('Occupied/Maintenance'),
                
                Tables\Filters\SelectFilter::make('resort_id')
                    ->relationship('resort', 'name')
                    ->label('Filter by Resort'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('View Details'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit Room'),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Delete Room'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccommodations::route('/'),
            'create' => Pages\CreateAccommodation::route('/create'),
            'edit' => Pages\EditAccommodation::route('/{record}/edit'),
        ];
    }
}