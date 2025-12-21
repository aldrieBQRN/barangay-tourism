<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResortResource\Pages;
use App\Models\Resort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists; // NEW: For the View Modal
use Filament\Infolists\Infolist; // NEW: For the View Modal

class ResortResource extends Resource
{
    protected static ?string $model = Resort::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 1: RESORT DETAILS ---
                Forms\Components\Section::make('Resort Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Describe amenities, location, etc.'),

                        // Caretaker Details Group
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('caretaker_name')
                                    ->label('Caretaker / Contact Person')
                                    ->prefixIcon('heroicon-m-user')
                                    ->placeholder('e.g. Mang Juan'),

                                Forms\Components\TextInput::make('contact_number')
                                    ->label('Contact Number')
                                    ->tel()
                                    ->prefixIcon('heroicon-m-phone')
                                    ->placeholder('09xx-xxx-xxxx'),
                            ])->columns(2)->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('resorts')
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Open for Business')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_boat_ride_needed')
                            ->label('Requires Boat Transfer?')
                            ->default(true)
                            ->live(),
                    ]),

                // --- SECTION 2: ENTRANCE FEES ---
                Forms\Components\Section::make('Entrance Fees')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('day_entrance')
                            ->label('Day Rate')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),
                        Forms\Components\TextInput::make('night_entrance')
                            ->label('Night Rate')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),
                    ]),

                // --- SECTION 3: BOAT FEES ---
                Forms\Components\Section::make('Boat Logic (Tiered Rates)')
                    ->description('Set the rules for boat transfers to this resort.')
                    ->visible(fn (Forms\Get $get) => $get('is_boat_ride_needed'))
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('boat_threshold')
                            ->label('Pax Threshold')
                            ->numeric()
                            ->default(6)
                            ->helperText('e.g., 6 people'),
                        Forms\Components\TextInput::make('boat_fixed_price')
                            ->label('Fixed Price')
                            ->numeric()
                            ->prefix('₱')
                            ->helperText('Below threshold'),
                        Forms\Components\TextInput::make('boat_per_head_price')
                            ->label('Per Head')
                            ->numeric()
                            ->prefix('₱')
                            ->helperText('At/Above threshold'),
                    ]),
            ]);
    }

    // --- NEW: INFOLIST (VIEW MODAL) ---
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Resort Profile')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('image')
                                ->circular()
                                ->grow(false),
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                Infolists\Components\TextEntry::make('description')
                                    ->markdown()
                                    ->prose(),
                            ]),
                        ])->from('md'),
                    ]),

                Infolists\Components\Section::make('Management Info')
                    ->schema([
                        Infolists\Components\TextEntry::make('caretaker_name')
                            ->label('Caretaker')
                            ->icon('heroicon-m-user'),
                        Infolists\Components\TextEntry::make('contact_number')
                            ->label('Contact No.')
                            ->icon('heroicon-m-phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'OPEN' : 'CLOSED'),
                        Infolists\Components\TextEntry::make('is_boat_ride_needed')
                            ->label('Boat Access')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'info' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'REQUIRED' : 'NOT NEEDED'),
                    ])->columns(4),

                Infolists\Components\Section::make('Pricing')
                    ->schema([
                        Infolists\Components\TextEntry::make('day_entrance')->money('PHP')->label('Day Entrance'),
                        Infolists\Components\TextEntry::make('night_entrance')->money('PHP')->label('Night Entrance'),
                        Infolists\Components\TextEntry::make('boat_fixed_price')
                            ->money('PHP')
                            ->label('Boat Fee')
                            ->placeholder('N/A'),
                    ])->columns(3),
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
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // CHANGED: Showing Caretaker Name instead of Contact Number
                Tables\Columns\TextColumn::make('caretaker_name')
                    ->label('Caretaker')
                    ->icon('heroicon-m-user')
                    ->searchable()
                    ->placeholder('None assigned'),

                Tables\Columns\IconColumn::make('is_boat_ride_needed')
                    ->label('Need Boat?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('day_entrance')
                    ->money('PHP')
                    ->label('Day Rate'),

                Tables\Columns\TextColumn::make('boat_fixed_price')
                    ->label('Boat Fee')
                    ->state(function (Resort $record) {
                        return $record->is_boat_ride_needed ? '₱' . number_format($record->boat_fixed_price, 2) : '-';
                    })
                    ->color(fn (Resort $record) => $record->is_boat_ride_needed ? 'primary' : 'gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Open Resorts')
                    ->falseLabel('Closed/Inactive'),

                Tables\Filters\TernaryFilter::make('is_boat_ride_needed')
                    ->label('Accessibility')
                    ->trueLabel('Boat Required')
                    ->falseLabel('Land Accessible'),
            ])
            ->actions([
                // UPDATED: Icons Only
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Details'),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Resort'),

                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Resort'),
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
            'index' => Pages\ListResorts::route('/'),
            'create' => Pages\CreateResort::route('/create'),
            'edit' => Pages\EditResort::route('/{record}/edit'),
        ];
    }
}