<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $recordTitleAttribute = 'guest_name';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 1: GUEST INFORMATION ---
                Forms\Components\Section::make('Guest Information')
                    ->description('Primary contact and demographic details.')
                    ->icon('heroicon-m-user')
                    ->schema([
                        // NEW: Reference Code (Read-Only, visible only on Edit)
                        Forms\Components\TextInput::make('booking_reference')
                            ->label('Reference Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $operation) => $operation === 'edit')
                            ->columnSpanFull()
                            ->prefixIcon('heroicon-m-qr-code'),

                        Forms\Components\TextInput::make('guest_name')
                            ->required()
                            ->label('Full Name')
                            ->prefixIcon('heroicon-m-user'),

                        Forms\Components\TextInput::make('contact_number')
                            ->label('Mobile Number')
                            ->tel()
                            ->placeholder('09xx-xxx-xxxx')
                            ->prefixIcon('heroicon-m-phone'),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->placeholder('guest@email.com')
                            ->prefixIcon('heroicon-m-envelope'),

                        Forms\Components\TextInput::make('origin')
                            ->label('City/Province of Origin')
                            ->placeholder('e.g. Manila, Cavite')
                            ->prefixIcon('heroicon-m-map-pin'),
                            
                        Forms\Components\Textarea::make('remarks')
                            ->label('Notes / Special Requests')
                            ->placeholder('e.g., Senior Citizen, PWD, Bringing Pets')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // --- SECTION 2: TRIP LOGISTICS ---
                Forms\Components\Section::make('Trip Logistics')
                    ->icon('heroicon-m-map-pin')
                    ->schema([
                        Forms\Components\Radio::make('stay_type')
                            ->label('Type of Visit')
                            ->options([
                                'day_tour' => 'Day Tour (8:00 AM - 5:00 PM)',
                                'overnight' => 'Overnight Stay',
                            ])
                            ->default('day_tour')
                            ->inline()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('check_out', null);
                                $set('nights', 0);
                            }),

                        Forms\Components\Toggle::make('is_island_hopping')
                            ->label('Include Island Hopping Tour?')
                            ->onColor('success')
                            ->inline(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),

                        Forms\Components\Select::make('resort_id')
                            ->relationship('resort', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('accommodation_id', null)),

                        Forms\Components\DatePicker::make('check_in')
                            ->label(fn (Get $get) => $get('stay_type') === 'overnight' ? 'Check-in Date' : 'Date of Visit')
                            ->default(now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),

                        Forms\Components\DatePicker::make('check_out')
                            ->label('Check-out Date')
                            ->required()
                            ->visible(fn (Get $get) => $get('stay_type') === 'overnight')
                            ->minDate(fn (Get $get) => $get('check_in'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $checkIn = $get('check_in');
                                $checkOut = $get('check_out');
                                if ($checkIn && $checkOut) {
                                    $days = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
                                    $set('nights', $days > 0 ? $days : 1);
                                } else {
                                    $set('nights', 1);
                                }
                                self::calculateTotal($get, $set);
                            }),

                        Forms\Components\Select::make('accommodation_id')
                            ->label('Room/Cottage')
                            ->options(function (Get $get) {
                                $resortId = $get('resort_id');
                                if (!$resortId) return [];
                                return \App\Models\Accommodation::where('resort_id', $resortId)
                                    ->where('is_available', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),

                        Forms\Components\TextInput::make('pax')
                            ->label('Total Guests')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),

                        Forms\Components\TextInput::make('nights')
                            ->numeric()
                            ->readOnly()
                            ->default(1)
                            ->label('Nights')
                            ->visible(fn (Get $get) => $get('stay_type') === 'overnight'), 
                    ])->columns(2),

                // --- SECTION 3: PARKING ---
                Forms\Components\Section::make('Parking Details')
                    ->icon('heroicon-m-truck')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('motor_count')
                            ->label('Motorcycles')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),

                        Forms\Components\TextInput::make('van_count')
                            ->label('Cars / Vans')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),
                    ])->columns(2),

                // --- SECTION 4: PAYMENT ---
                Forms\Components\Section::make('Payment Details')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('subtotal_eco_fee')
                                    ->label('Eco Fee')
                                    ->numeric()->prefix('₱')->readOnly(),
                                
                                Forms\Components\TextInput::make('subtotal_boat_fee')
                                    ->label('Boat Fee')
                                    ->numeric()->prefix('₱')->readOnly(),

                                Forms\Components\TextInput::make('subtotal_accommodation_fee')
                                    ->label('Room/Cottage Fee')
                                    ->numeric()->prefix('₱')->readOnly(),

                                Forms\Components\TextInput::make('subtotal_island_hopping')
                                    ->label('Island Hopping')
                                    ->numeric()->prefix('₱')->readOnly(),

                                Forms\Components\TextInput::make('subtotal_parking_fee')
                                    ->label('Parking Fee')
                                    ->numeric()->prefix('₱')->readOnly(),
                            ])->columns(2), 

                        Forms\Components\Placeholder::make('separator')
                            ->hiddenLabel()
                            ->content(new \Illuminate\Support\HtmlString('<div style="border-top: 1px solid #ddd; margin: 15px 0;"></div>')),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('GRAND TOTAL')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->readOnly()
                                    ->extraInputAttributes(['style' => 'font-weight: 900; font-size: 1.5em; color: #166534; background-color: #f0fdf4; text-align: right;']),

                                Forms\Components\Split::make([
                                    Forms\Components\Select::make('payment_method')
                                        ->options([
                                            'cash' => 'Cash',
                                            'gcash' => 'GCash / E-Wallet',
                                            'bank_transfer' => 'Bank Transfer',
                                        ])
                                        ->default('cash')
                                        ->required(),

                                    Forms\Components\Select::make('status')
                                        ->options([
                                            'unpaid' => 'Unpaid',
                                            'paid' => 'Paid',
                                        ])
                                        ->default('unpaid')
                                        ->required()
                                        ->native(false),
                                ]),
                            ])->columnSpanFull(),
                    ]),
            ]);
    }

    // --- INFOLIST (VIEW MODAL) ---
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Guest Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('booking_reference')
                            ->label('Ref Code')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('guest_name')->weight('bold')->icon('heroicon-m-user'),
                        Infolists\Components\TextEntry::make('contact_number')->icon('heroicon-m-phone')->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('email')->icon('heroicon-m-envelope')->placeholder('N/A'),
                    ])->columns(4),

                Infolists\Components\Section::make('Trip Logistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('resort.name')->label('Destination')->icon('heroicon-m-map-pin'),
                        Infolists\Components\TextEntry::make('stay_type')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'overnight' ? 'purple' : 'info')
                            ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),
                        Infolists\Components\TextEntry::make('is_island_hopping')
                            ->label('Island Hopping')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'YES' : 'NO'),
                        Infolists\Components\TextEntry::make('check_in')->date(),
                        Infolists\Components\TextEntry::make('check_out')->date()->placeholder('N/A (Day Tour)'),
                        Infolists\Components\TextEntry::make('accommodation.name')->label('Room/Cottage')->placeholder('None'),
                        Infolists\Components\TextEntry::make('pax')->label('Total Guests'),
                    ])->columns(3),

                Infolists\Components\Section::make('Payment Breakdown')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal_eco_fee')->label('Eco Fee')->money('PHP'),
                        Infolists\Components\TextEntry::make('subtotal_boat_fee')->label('Boat Fee')->money('PHP'),
                        Infolists\Components\TextEntry::make('subtotal_accommodation_fee')->label('Room/Cottage')->money('PHP'),
                        Infolists\Components\TextEntry::make('subtotal_island_hopping')->label('Island Hop')->money('PHP'),
                        Infolists\Components\TextEntry::make('subtotal_parking_fee')->label('Parking')->money('PHP'),

                        Infolists\Components\TextEntry::make('motor_count')->label('Motorcycles'),
                        Infolists\Components\TextEntry::make('van_count')->label('Cars/Vans'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) { 'paid' => 'success', 'unpaid' => 'danger' }),
                            
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('GRAND TOTAL')
                            ->money('PHP')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('success'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('remarks')->placeholder('No remarks provided.'),
                    ])
            ]);
    }

    // --- CALCULATOR HELPER ---
    public static function calculateTotal(Get $get, Set $set)
    {
        $resortId = $get('resort_id');
        $resort = $resortId ? Resort::find($resortId) : null;
        
        $pax = (int) $get('pax');
        $stayType = $get('stay_type');
        $nights = (int) $get('nights');
        $multiplier = ($stayType === 'overnight') ? max(1, $nights) : 1;

        $settings = Setting::first();
        $ecoFeeRate = $settings ? $settings->eco_fee : 20;
        $motorRate = $settings ? $settings->parking_motor_rate : 50;
        $vanRate = $settings ? $settings->parking_van_rate : 150;
        
        $ihSmall = $settings ? $settings->island_hopping_small_rate : 1500;
        $ihMedium = $settings ? $settings->island_hopping_medium_rate : 2500;
        $ihLarge = $settings ? $settings->island_hopping_large_rate : 3500;

        // 1. Eco Fee
        $ecoTotal = $pax * $ecoFeeRate;
        $set('subtotal_eco_fee', $ecoTotal);

        // 2. Boat Fee
        $boatTotal = 0;
        if ($resort && $resort->is_boat_ride_needed) {
            if ($pax < ($resort->boat_threshold ?? 6)) {
                $boatTotal = $resort->boat_fixed_price ?? 3000;
            } else {
                $boatTotal = $pax * ($resort->boat_per_head_price ?? 300);
            }
        }
        $set('subtotal_boat_fee', $boatTotal);

        // 3. Accommodation
        $accomTotal = 0;
        if ($roomId = $get('accommodation_id')) {
            $room = \App\Models\Accommodation::find($roomId);
            if ($room) {
                if ($stayType === 'overnight') {
                    $accomTotal = ($room->price_per_night ?? 0) * $multiplier;
                } else {
                    $accomTotal = $room->price_day_tour ?? $room->price_per_night ?? 0;
                }
            }
        }
        $set('subtotal_accommodation_fee', $accomTotal);

        // 4. Island Hopping
        $islandHoppingTotal = 0;
        if ($get('is_island_hopping')) {
            if ($pax <= 4) {
                $islandHoppingTotal = $ihSmall;
            } elseif ($pax <= 10) {
                $islandHoppingTotal = $ihMedium;
            } else {
                $islandHoppingTotal = $ihLarge;
            }
        }
        $set('subtotal_island_hopping', $islandHoppingTotal);

        // 5. Parking
        $motorCount = (int) $get('motor_count');
        $vanCount = (int) $get('van_count');
        $parkingTotal = ($motorCount * $motorRate) + ($vanCount * $vanRate);
        $set('subtotal_parking_fee', $parkingTotal);

        $grandTotal = $ecoTotal + $boatTotal + $accomTotal + $parkingTotal + $islandHoppingTotal;
        $set('total_amount', $grandTotal);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // UPDATED: Booking Reference Column
                Tables\Columns\TextColumn::make('booking_reference')
                    ->label('Ref #')
                    ->searchable() // Allows scanner to find it!
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('M d, Y')->sortable(),
                Tables\Columns\TextColumn::make('guest_name')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('resort.name')->label('Destination')->sortable(),
                
                Tables\Columns\TextColumn::make('guest_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'checked_in' => 'success',
                        'checked_out' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),

                Tables\Columns\TextColumn::make('pax')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('total_amount')->money('PHP')->sortable()->weight('bold'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) { 'paid' => 'success', 'unpaid' => 'danger' }),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('resort_id')->relationship('resort', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-m-arrow-right-end-on-rectangle')
                    ->color('success')
                    ->button()
                    ->visible(fn (Booking $record) => $record->guest_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Booking $record) {
                        $record->update(['guest_status' => 'checked_in']);
                        Notification::make()->title('Guest Checked In')->success()->send();
                    }),

                Tables\Actions\Action::make('check_out')
                    ->label('Check Out')
                    ->icon('heroicon-m-arrow-left-start-on-rectangle')
                    ->color('warning')
                    ->button()
                    ->visible(fn (Booking $record) => $record->guest_status === 'checked_in')
                    ->form([
                        Forms\Components\Placeholder::make('warning')
                            ->label('Payment Status')
                            ->content(fn (Booking $record) => $record->status === 'paid' 
                                ? '✅ Fully Paid' 
                                : '❌ GUEST IS UNPAID! Collect payment now.')
                            ->extraAttributes(fn (Booking $record) => [
                                'class' => $record->status === 'unpaid' ? 'text-danger-600 font-bold' : 'text-success-600 font-bold',
                            ]),
                        Forms\Components\Toggle::make('mark_as_paid')
                            ->label('Mark as Paid & Check Out')
                            ->visible(fn (Booking $record) => $record->status === 'unpaid')
                            ->required(),
                    ])
                    ->action(function (Booking $record, array $data) {
                        if ($record->status === 'unpaid' && !($data['mark_as_paid'] ?? false)) {
                            return; 
                        }
                        $updates = ['guest_status' => 'checked_out'];
                        if ($data['mark_as_paid'] ?? false) $updates['status'] = 'paid';
                        
                        $record->update($updates);
                        Notification::make()->title('Guest Checked Out')->success()->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('print')
                        ->icon('heroicon-o-printer')
                        ->label('Print Receipt')
                        ->action(function (Booking $record) {
                            return response()->streamDownload(function () use ($record) {
                                echo Pdf::loadHTML(Blade::render('bookings.receipt', ['record' => $record]))->stream();
                            }, "receipt-{$record->booking_reference}.pdf");
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('More Actions'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}