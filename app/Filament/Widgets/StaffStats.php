<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StaffStats extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // ONLY Staff sees these 3 cards
        return Auth::check() && $user->hasRole('Staff');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Guests Today', Booking::whereDate('check_in', now())->where('guest_status', 'checked_in')->sum('pax'))
                ->description('Total arrived')
                ->color('success'),

            Stat::make('Expected Today', Booking::whereDate('check_in', now())->sum('pax') . ' Pax')
                ->color('info'),

            Stat::make('Pending Check-ins', Booking::whereDate('check_in', now())->where('guest_status', 'pending')->count())
                ->color('warning'),
        ];
    }
}