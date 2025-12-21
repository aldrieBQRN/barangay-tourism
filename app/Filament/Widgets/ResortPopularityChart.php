<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResortPopularityChart extends ChartWidget
{
    protected static ?string $heading = 'Most Popular Resorts';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Auth::check() && $user->hasRole('super_admin');
    }

    protected function getData(): array
    {
        $data = Booking::select('resort_id', DB::raw('count(*) as total'))
            ->groupBy('resort_id')
            ->with('resort')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Bookings',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#1d4ed8',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->map(fn ($booking) => $booking->resort->name),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}