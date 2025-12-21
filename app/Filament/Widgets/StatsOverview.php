<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Resort;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // ONLY Admin sees these 6 cards
        return Auth::check() && $user->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        return [
            // ROW 1: Financial & System
            Stat::make('Today\'s Revenue', '₱' . number_format(Booking::whereDate('created_at', now())->where('status', 'paid')->sum('total_amount'), 2))
                ->description('Total collected today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Monthly Income', '₱' . number_format(Booking::whereMonth('created_at', now()->month)->where('status', 'paid')->sum('total_amount'), 2))
                ->description('Total for ' . now()->format('F'))
                ->color('success'),

            Stat::make('Total Resorts', Resort::count())
                ->descriptionIcon('heroicon-m-home-modern'),

            // ROW 2: Operational Progress
            Stat::make('Guests Today', Booking::whereDate('check_in', now())->where('guest_status', 'checked_in')->sum('pax'))
                ->description('Total individuals arrived')
                ->color('success'),

            Stat::make('Expected Today', Booking::whereDate('check_in', now())->sum('pax') . ' Pax')
                ->description('Total arrivals scheduled')
                ->color('info'),

            Stat::make('Pending Check-ins', Booking::whereDate('check_in', now())->where('guest_status', 'pending')->count())
                ->description('Groups yet to scan')
                ->color('warning'),
        ];
    }
}