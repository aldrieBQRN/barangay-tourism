<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GuestOriginChart extends ChartWidget
{
    protected static ?string $heading = 'Where are guests coming from?';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Auth::check() && $user->hasRole('super_admin');
    }

    protected function getData(): array
    {
        $data = Booking::select('origin', DB::raw('count(*) as total'))
            ->whereNotNull('origin') // Ignore empty origins
            ->groupBy('origin')
            ->orderByDesc('total')
            ->limit(5) // Only show top 5 cities
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Guests',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#fca5a5', '#fdba74', '#fcd34d', '#86efac', '#93c5fd'
                    ],
                ],
            ],
            'labels' => $data->pluck('origin'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}