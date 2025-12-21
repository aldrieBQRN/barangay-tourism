<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookings extends BaseWidget
{
    protected static ?int $sort = 5; // Show at the bottom
    
    // Make it span the full width of the screen
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Get the latest 5 bookings
                Booking::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked On')
                    ->dateTime('M d, h:i A'),
                
                Tables\Columns\TextColumn::make('guest_name')
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('resort.name')
                    ->label('Destination'),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Visit Date')
                    ->date('M d'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                    }),
            ])
            ->paginated(false); // Hide page numbers (it's just a snapshot)
    }
}